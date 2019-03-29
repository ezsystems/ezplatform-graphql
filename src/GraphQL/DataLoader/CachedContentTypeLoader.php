<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\DataLoader;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * @internal
 */
class CachedContentTypeLoader implements ContentTypeLoader
{
    /**
     * @var ContentTypeLoader
     */
    private $innerLoader;

    /**
     * @var ContentType[]
     */
    private $loadedItems = [];

    public function __construct(ContentTypeLoader $innerLoader)
    {
        $this->innerLoader = $innerLoader;
    }

    public function load($contentTypeId): ContentType
    {
        if (!isset($this->loadedItems[$contentTypeId])) {
            $this->loadedItems[$contentTypeId] = $this->innerLoader->load($contentTypeId);
        }

        return $this->loadedItems[$contentTypeId];
    }

    public function loadByIdentifier($identifier): ContentType
    {
        $contentType = $this->innerLoader->loadByIdentifier($identifier);

        if (!isset($this->innerLoader[$contentType->id])) {
            $this->innerLoader[$contentType->id] = $contentType;
        }

        return $contentType;
    }
}
