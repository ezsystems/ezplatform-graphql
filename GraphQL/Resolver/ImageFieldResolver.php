<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\SPI\Variation\VariationHandler;
use Overblog\GraphQLBundle\Error\UserError;
use eZ\Publish\Core\FieldType\Image\Value as ImageFieldValue;

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
     * @var array
     */
    private $variations;

    public function __construct(VariationHandler $variationHandler, ContentService $contentService, array $variations)
    {
        $this->variationHandler = $variationHandler;
        $this->contentService = $contentService;
        $this->variations = $variations;
    }

    public function resolveImageVariation(ImageFieldValue $fieldValue, $args)
    {
        $idArray = explode('-', $fieldValue->imageId);
        if (count($idArray) != 2) {
            throw new UserError("Invalid image ID {$fieldValue->imageId}");
        }
        list($contentId, $fieldId) = $idArray;

        $content = $this->contentService->loadContent($contentId);

        $fieldFound = false;
        /** @var $field \eZ\Publish\API\Repository\Values\Content\Field */
        foreach ($content->getFields() as $field) {
            if ($field->id == $fieldId) {
                $fieldFound = true;
                break;
            }
        }

        if (!$fieldFound) {
            throw new UserError("No image field with ID $fieldId could be found");
        }

        // check the field's value
        if ($field->value->uri === null) {
            throw new UserError("Image file {$field->value->id} doesn't exist");
        }

        $variations = [];
        foreach ($args['identifier'] as $identifier) {
            $variations[] = $this->variationHandler->getVariation(
                $field,
                $this->contentService->loadVersionInfo($content->contentInfo),
                $identifier
            );
        }

        return $variations;
    }
}
