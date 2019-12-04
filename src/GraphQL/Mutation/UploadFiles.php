<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\FieldType;
use EzSystems\EzPlatformAdminUi\UI\Config\Provider\ContentTypeMappings;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFiles
{
    /**
     * @var ContentTypeMappings
     */
    private $contentTypeMappings;
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository, ContentTypeMappings $contentTypeMappings)
    {
        $this->repository = $repository;
        $this->contentTypeMappings = $contentTypeMappings;
    }

    public function uploadFiles(Argument $args)
    {
        $createdContent = [];
        $warnings = [];

        foreach ($args['files'] as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $mimeType = $file->getMimeType();
            $mapping = $this->mapAgainstConfig($mimeType);

            try {
                $createdContent[] = $this->createContent($mapping, $file, $args['locationId'], $args['language']);
            } catch (\Exception $e) {
                $warnings[] = $e->getMessage();
            }
        }

        return ['files' => $createdContent, 'warnings' => $warnings];
    }

    private function mapAgainstConfig($mimeType)
    {
        foreach ($this->contentTypeMappings->getConfig()['defaultMappings'] as $mapping) {
            if (in_array($mimeType, $mapping['mimeTypes'])) {
                return array_filter(
                    $mapping,
                    function ($key) {
                        return in_array($key, ['contentTypeIdentifier', 'contentFieldIdentifier', 'nameFieldIdentifier']);
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }
        }

        return $this->contentTypeMappings->getConfig()['fallbackContentType'];
    }

    /**
     * @param array $mapping The upload mapping for this file (array with ContentTypeIdentifier, nameFieldIdentifier...)
     * @param $locationId The parent location ID
     * @param $languageCode
     */
    private function createContent(array $mapping, UploadedFile $file, $locationId, $languageCode): ContentInfo
    {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier($mapping['contentTypeIdentifier']);
        $struct = $contentService->newContentCreateStruct($contentType, $languageCode);
        $struct->setField($mapping['nameFieldIdentifier'], $file->getClientOriginalName());

        $fieldDefinition = $contentType->getFieldDefinition($mapping['contentFieldIdentifier']);
        switch ($fieldDefinition->fieldTypeIdentifier) {
            case 'ezimage':
                $valueType = FieldType\Image\Value::class;
                break;
            case 'ezbinaryfile':
                $valueType = FieldType\BinaryFile\Value::class;
                break;
            case 'ezmedia':
                $valueType = FieldType\Media\Value::class;
                break;
            default:
                throw new UserError('FieldType not supported for upload');
        }
        $struct->setField(
            $mapping['contentFieldIdentifier'],
            new $valueType([
                'fileName' => $file->getClientOriginalName(),
                'inputUri' => $file->getPathname(),
                'fileSize' => $file->getSize(),
            ])
        );

        $draft = $contentService->createContent($struct, [$locationService->newLocationCreateStruct($locationId)]);
        $content = $contentService->publishVersion($draft->getVersionInfo());

        return $content->contentInfo;
    }
}
