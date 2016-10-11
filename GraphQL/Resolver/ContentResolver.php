<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use BD\EzPlatformGraphQLBundle\GraphQL\Value\ContentFieldValue;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

class ContentResolver
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    public function __construct(ContentService $contentService, SearchService $searchService, ContentTypeService $contentTypeService, TypeResolver $typeResolver)
    {
        $this->contentService = $contentService;
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
        $this->typeResolver = $typeResolver;
    }

    public function findContentByType($contentTypeId)
    {
        $searchResults = $this->searchService->findContentInfo(
            new Query([
                'filter' => new Query\Criterion\ContentTypeId($contentTypeId)
            ])
        );

        return array_map(
            function(SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResults->searchHits
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function findContentRelations(ContentInfo $contentInfo, $version = null)
    {
        return $this->contentService->loadRelations(
            $this->contentService->loadVersionInfo($contentInfo, $version)
        );
    }

    public function findContentReverseRelations(ContentInfo $contentInfo, $version = null)
    {
        return $this->contentService->loadReverseRelations($contentInfo);
    }

    public function resolveContent($args)
    {
        if (isset($args['id'])) {
            return $this->contentService->loadContentInfo($args['id']);
        }

        if (isset($args['remoteId'])) {
            return $this->contentService->loadContentInfoByRemoteId($args['remoteId']);
        }
    }

    public function resolveContentById($contentId)
    {
        return $this->contentService->loadContentInfo($contentId);
    }

    public function resolveContentFields($contentId, $args)
    {
        $content = $this->contentService->loadContent(
            $contentId,
            isset($args['languages']) ? $args['languages'] : null,
            isset($args['version']) ? $args['version'] : null,
            isset($args['useAlwaysAvailable']) ? $args['useAlwaysAvailable'] : true
        );

        if (isset($args['identifier'])) {
            $field = $content->getField($args['identifier']);
            $value = new ContentFieldValue(
                [
                    'contentId' => $contentId,
                    'fieldDefIdentifier' => $field->fieldDefIdentifier,
                    'value' => $field->value,
                ]
            );
            return [
                new Field(
                    [
                        'id' => $field->id,
                        'value' => $value,
                        'fieldDefIdentifier' => $field->fieldDefIdentifier,
                        'languageCode' => $field->languageCode,
                    ]
                )
            ];
        }

        return array_map(
            function(Field $field) use ($contentId) {
                $value = new ContentFieldValue(
                    [
                        'contentId' => $contentId,
                        'fieldDefIdentifier' => $field->fieldDefIdentifier,
                        'value' => $field->value,
                    ]
                );
                return new Field(
                    [
                        'id' => $field->id,
                        'value' => $value,
                        'fieldDefIdentifier' => $field->fieldDefIdentifier,
                        'languageCode' => $field->languageCode,
                    ]
                );
            },
            $content->getFieldsByLanguage()
        );
    }

    public function resolveContentFieldsInVersion($contentId, $versionNo, $args)
    {
        return $this->resolveContentFields(
            $contentId,
            ['version' => $versionNo] + $args->getRawArguments()
        );
    }

    public function resolveContentVersions($contentId)
    {
        return $this->contentService->loadVersions(
            $this->contentService->loadContentInfo($contentId)
        );
    }

    public function resolveFieldValueType(ContentFieldValue $field)
    {
        $contentInfo = $this->contentService->loadContentInfo($field->contentId);
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        $fieldDefinition = $contentType->getFieldDefinition($field->fieldDefIdentifier);

        $typesMap = [
            'ezauthor' => 'AuthorFieldValue',
            'ezstring' => 'TextLineFieldValue',
            'ezimage' => 'ImageFieldValue',
            'ezrichtext' => 'RichTextFieldValue',
        ];

        $typeString =
            isset($typesMap[$fieldDefinition->fieldTypeIdentifier]) ?
            $typesMap[$fieldDefinition->fieldTypeIdentifier] :
            'GenericFieldValue';

        return $this->typeResolver->resolve($typeString);
    }
}
