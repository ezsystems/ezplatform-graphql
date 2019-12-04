<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use Exception;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\Variation\VariationHandler;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;

final class ContentThumbnailResolver
{
    const THUMBNAIL_VARIATION_IDENTIFIER = 'medium';

    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader
     */
    private $contentLoader;
    /**
     * @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper
     */
    private $assetMapper;
    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    private $variationHandler;
    /**
     * @var \eZ\Publish\Core\FieldType\Image\Type
     */
    private $imageFieldType;

    public function __construct(
        FieldType\Image\Type $imageFieldType,
        ContentLoader $contentLoader,
        FieldType\ImageAsset\AssetMapper $assetMapper,
        VariationHandler $variationHandler)
    {
        $this->contentLoader = $contentLoader;
        $this->assetMapper = $assetMapper;
        $this->variationHandler = $variationHandler;
        $this->imageFieldType = $imageFieldType;
    }

    /**
     * @return array|null array with the thumbnail info, or null if no thumbnail could be obtained for that image
     */
    public function resolveContentThumbnail(Content $content): ?array
    {
        try {
            $imageField = $this->getThumbnailImageField($content);
        } catch (Exception $e) {
            return null;
        }

        if ($imageField === null || $this->imageFieldType->isEmptyValue($imageField->value)) {
            return null;
        }

        $thumbnailVariation = $this->variationHandler->getVariation($imageField, $content->versionInfo, self::THUMBNAIL_VARIATION_IDENTIFIER);

        return [
            'uri' => $thumbnailVariation->uri,
            'width' => $thumbnailVariation->width,
            'height' => $thumbnailVariation->height,
            'alternativeText' => $imageField->value->alternativeText,
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function getThumbnailImageField(Content $content): Field
    {
        foreach ($content->getFieldsByLanguage() as $field) {
            if ($field->fieldTypeIdentifier === 'ezimage') {
                return $field;
            } elseif ($field->fieldTypeIdentifier === 'ezimageasset') {
                $assetContent = $this->contentLoader->findSingle(new Criterion\ContentId($field->value->destinationContentId));

                return $this->assetMapper->getAssetField($assetContent);
            } elseif ($field->fieldTypeIdentifier === 'ezobjectrelation') {
                $relatedContent = $this->contentLoader->findSingle(new Criterion\ContentId($field->value->destinationContentId));

                return $this->getThumbnailImageField($relatedContent);
            }
        }

        foreach ($content->getFieldsByLanguage() as $field) {
            if ($field->fieldTypeIdentifier === 'ezobjectrelation') {
                $relatedContent = $this->contentLoader->findSingle(new Criterion\ContentId($field->value->destinationContentId));

                return $this->getThumbnailImageField($relatedContent);
            }
        }

        throw new Exception("Content doesn't have an image compatible field");
    }
}
