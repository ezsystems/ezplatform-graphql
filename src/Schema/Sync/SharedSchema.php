<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

interface SharedSchema
{
    /**
     * Adds a file for publishing.
     */
    public function addFile(string $name, string $contents);

    /**
     * Publishes the schema.
     */
    public function publish(int $timestamp);

    /**
     * Gets the file from a published schema.
     * @return array file name => file contents
     * @throws \Exception if the directory is not found
     */
    public function getFiles(int $timestamp);
}
