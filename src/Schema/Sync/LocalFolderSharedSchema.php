<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LocalFolderSharedSchema implements SharedSchema
{
    /**
     * @var string
     */
    private $sharedDirectory;

    /**
     * @var array Map of filename => file contents
     */
    private $files = [];

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Sync\TimestampHandler
     */
    private $timestampHandler;

    public function __construct(TimestampHandler $timestampHandler, string $sharedDirectory)
    {
        $this->sharedDirectory = rtrim($sharedDirectory, '/');
        $this->timestampHandler = $timestampHandler;
    }

    public function addFile(string $name, string $contents)
    {
        $this->files[$name] = $contents;
    }

    public function publish(int $timestamp)
    {
        $fs = new Filesystem();

        $targetDirectory = "$this->sharedDirectory/$timestamp";
        $fs->mkdir($targetDirectory);

        foreach ($this->files as $name => $contents) {
            $fs->dumpFile("$targetDirectory/$name", $contents);
        }

        $this->timestampHandler->set($timestamp);
    }

    public function getFiles(int $timestamp): array
    {
        $directory = "$this->sharedDirectory/$timestamp";
        if (!file_exists($directory) || !is_dir($directory)) {
            throw new \Exception("Directory not found");
        }

        $files = [];
        foreach ((new Finder())->files()->in($directory) as $file) {
            $files[$file->getBasename()] = $file->getContents();
        }

        return $files;
    }
}
