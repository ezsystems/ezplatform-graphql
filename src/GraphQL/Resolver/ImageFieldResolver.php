<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\FieldType\Image\Value as ImageFieldValue;
use eZ\Publish\SPI\Variation\VariationHandler;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use Overblog\GraphQLBundle\Error\UserError;

/**
 * @internal
 */
class ImageFieldResolver
{
    /**
     * @var \eZ\Publish\SPI\Variation\VariationHandler
     */
    private $variationHandler;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;
    /**
     * @var FieldType\Image\Type
     */
    private $fieldType;
    /**
     * @var ContentLoader
     */
    private $contentLoader;

    public function __construct(
        FieldType\Image\Type $imageFieldType,
        VariationHandler $variationHandler,
        ContentLoader $contentLoader,
        ContentService $contentService
    ) {
        $this->variationHandler = $variationHandler;
        $this->contentService = $contentService;
        $this->fieldType = $imageFieldType;
        $this->contentLoader = $contentLoader;
    }

    public function resolveImageVariations(ImageFieldValue $fieldValue, $args)
    {
        if ($this->fieldType->isEmptyValue($fieldValue)) {
            return null;
        }
        list($content, $field) = $this->getImageField($fieldValue);

        $variations = [];
        foreach ($args['identifier'] as $identifier) {
            $variations[] = $this->variationHandler->getVariation($field, $content->versionInfo, $identifier);
        }

        return $variations;
    }

    public function resolveImageVariation(ImageFieldValue $fieldValue, $args)
    {
        if ($this->fieldType->isEmptyValue($fieldValue)) {
            return null;
        }

        list($content, $field) = $this->getImageField($fieldValue);
        $versionInfo = $this->contentService->loadVersionInfo($content->contentInfo);

        return $this->variationHandler->getVariation($field, $versionInfo, $args['identifier']);
    }

    /**
     * @return [Content, Field]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function getImageField(ImageFieldValue $fieldValue): array
    {
        list($contentId, $fieldId) = $this->decomposeImageId($fieldValue);

        $content = $this->contentLoader->findSingle(new Criterion\ContentId($contentId));

        $fieldFound = false;
        /** @var $field \eZ\Publish\API\Repository\Values\Content\Field */
        foreach ($content->getFields() as $field) {
            if ($field->id == $fieldId) {
                $fieldFound = true;
                break;
            }
        }

        if (!$fieldFound) {
            throw new UserError("Could not find an image Field with ID $fieldId");
        }

        // check the field's value
        if ($field->value->uri === null) {
            throw new UserError("Image file {$field->value->id} doesn't exist");
        }

        return [$content, $field];
    }

    protected function decomposeImageId(ImageFieldValue $fieldValue): array
    {
        $idArray = explode('-', $fieldValue->imageId);
        if (count($idArray) != 3) {
            throw new UserError("Invalid image ID {$fieldValue->imageId}");
        }

        return $idArray;
    }
}
