<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;

final class ContentThumbnailResolver
{
    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy */
    private $thumbnailStrategy;

    public function __construct(
        ThumbnailStrategy $thumbnailStrategy
    ) {
        $this->thumbnailStrategy = $thumbnailStrategy;
    }

    /**
     * @return array|null array with the thumbnail info, or null if no thumbnail could be obtained for that image
     */
    public function resolveContentThumbnail(Content $content): ?array
    {
        $thumbnail = $this->thumbnailStrategy->getThumbnail(
            $content->getContentType(),
            $content->getFields(),
            $content->getVersionInfo()
        );

        if ($thumbnail === null) {
            return null;
        }

        return [
            'uri' => $thumbnail->resource,
            'width' => $thumbnail->width,
            'height' => $thumbnail->height,
            'mimeType' => $thumbnail->mimeType,
            'alternativeText' => '',
        ];
    }
}
