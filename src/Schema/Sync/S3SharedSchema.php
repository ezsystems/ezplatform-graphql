<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use EzSystems\EzPlatformGraphQL\Schema\Sync\SharedSchema;
use EzSystems\EzPlatformGraphQL\Schema\Sync\TimestampHandler;
use Symfony\Component\Finder\Finder;

class S3SharedSchema implements SharedSchema
{
    /**
     * @var array Map of filename => file contents
     */
    private $files = [];

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Sync\TimestampHandler
     */
    private $timestampHandler;
    /**
     * @var \Aws\S3\S3Client
     */
    private $s3;

    private $bucket;

    public function __construct(TimestampHandler $timestampHandler, S3Client $graphQLSyncS3Client, string $bucket)
    {
        $this->timestampHandler = $timestampHandler;
        $this->s3 = $graphQLSyncS3Client;
        $this->bucket = $bucket;
    }

    public function addFile(string $name, string $contents)
    {
        $this->files[$name] = $contents;
    }

    public function publish(int $timestamp)
    {
        foreach ($this->files as $name => $contents) {
            $this->putFileToS3("$timestamp/$name", $contents);
        }

        $this->timestampHandler->set($timestamp);
    }

    public function getFiles(int $timestamp): array
    {
        if (!$this->hasFileOnS3("$timestamp/__classes.map")) {
            throw new \Exception("Shared schema not found");
        }

        $files = [];

        $prefix = "$timestamp/";
        $listResult = $this->s3->listObjectsV2(['Bucket' => $this->bucket, 'Prefix' => $prefix]);
        foreach ($listResult->get('Contents') as $listItem) {
            $fileResult = $this->s3->getObject(['Bucket' => $this->bucket, 'Key' => $listItem['Key']]);
            $fileName = str_replace($prefix, '', $listItem['Key']);
            $files[$fileName] = $fileResult->get('Body')->getContents();
        }

        return $files;
    }

    private function putFileToS3(string $name, string $contents): void
    {
        try {
            $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $name,
                'Body' => $contents,
            ]);
        } catch (S3Exception $e) {
            throw new \Exception("Error creating file", 0, $e);
        }
    }

    private function hasFileOnS3(string $path): bool
    {
        try {
            $this->s3->getObject(['Bucket' => $this->bucket, 'Key' => $path]);
        } catch (S3Exception $e) {
            return false;
        }

        return true;
    }
}
