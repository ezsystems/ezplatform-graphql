<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use Exception;
use eZ\Publish\API\Repository\Values\Content\Content;

final class ContentThumbnailResolver
{
    /**
     * @return array|null array with the thumbnail info, or null if no thumbnail could be obtained for that image
     */
    public function resolveContentThumbnail(Content $content): ?array
    {
        try {
            $thumbnail = $content->getThumbnail();
        } catch (Exception $e) {
            return null;
        }

        return [
            'uri' => $thumbnail->resource,
            'width' => $thumbnail->width,
            'height' => $thumbnail->height,
            'alternativeText' => '',
        ];
    }
}
