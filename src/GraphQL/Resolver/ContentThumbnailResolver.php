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
    /**
     * @param Content $content
     *
     * @return array|null array with the thumbnail info, or null if no thumbnail could be obtained for that image
     */
    public function resolveContentThumbnail(Content $content): ?array
    {
        $thumbnailUri = $content->getThumbnail();
        $width = $height = null;
        if (pathinfo($thumbnailUri, PATHINFO_EXTENSION) !== 'svg') {
            list($width, $height) = getimagesize($thumbnailUri);
        }

        return [
            'uri' => $thumbnailUri,
            'width' => $width,
            'height' => $height,
            'alternativeText' => $content->getName(),
        ];
    }
}
