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

    /**
     * @var array
     */
    private $identifierToIdMap = [];

    /**
     * @var ContentType[]
     */
    private $loadedItemsByIdentifier = [];

    public function __construct(ContentTypeLoader $innerLoader)
    {
        $this->innerLoader = $innerLoader;
    }

    public function load($contentTypeId): ContentType
    {
        if (!isset($this->loadedItems[$contentTypeId])) {
            $contentType = $this->innerLoader->load($contentTypeId);
            $this->loadedItems[$contentTypeId] = $contentType;
            $this->identifierToIdMap[$contentType->identifier] = $contentTypeId;
        }

        return $this->loadedItems[$contentTypeId];
    }

    public function loadByIdentifier($identifier): ContentType
    {
        if (!isset($this->identifierToIdMap[$identifier])) {
            $contentType = $this->innerLoader->loadByIdentifier($identifier);
            $this->loadedItems[$contentType->id] = $contentType;
            $this->identifierToIdMap[$identifier] = $contentType->id;
        }


        return $this->loadedItems[$this->identifierToIdMap[$identifier]];
    }
}
