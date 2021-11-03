<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockFactory;

/**
 * Updates the local graphql schema based on a remote one shared by the reference server.
 */
class UpdateSchemaIfNeeded implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\Lock\LockFactory
     */
    private $lockFactory;

    /**
     * @var string
     */
    private $definitionsDirectory;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Sync\TimestampHandler
     */
    private $timestampHandler;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Sync\SharedSchema
     */
    private $sharedSchema;

    public function __construct(string $definitionsDirectory, SharedSchema $sharedSchema, TimestampHandler $timestampHandler, ?LoggerInterface $graphqlLogger, LockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;
        $this->logger = $graphqlLogger;
        $this->definitionsDirectory = $definitionsDirectory;
        $this->timestampHandler = $timestampHandler;
        $this->sharedSchema = $sharedSchema;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [Events::PRE_EXECUTOR => ['updateSchema', 10]];
    }

    public function updateSchema(ExecutorArgumentsEvent $event)
    {
        $lock = $this->lockFactory->createLock('graphql_schema_sync');
        if (!$lock->acquire()) {
            return;
        }

        $localSchemaTimestamp = filemtime("$this->definitionsDirectory/__classes.map");
        $remoteSchemaTimestamp = $this->timestampHandler->get();
        if ($remoteSchemaTimestamp === false || $remoteSchemaTimestamp < $localSchemaTimestamp) {
            $this->logger->info("No update is needed");
            return;
        }

        $this->logger->info("Update needed with timestamp $remoteSchemaTimestamp");

        $newSchemaPath = $this->updateSchemaFromSharedResource($remoteSchemaTimestamp);

        $installSchemaCallback = function() use($newSchemaPath, $lock) {
            if ($this->logger) {
                $this->logger->info("Applying the updated schema ($newSchemaPath -> $this->definitionsDirectory)");
            }
            $fs = new FileSystem();
            $fs->remove($this->definitionsDirectory);
            $fs->rename($newSchemaPath, $this->definitionsDirectory);
            $lock->release();
        };
        register_shutdown_function($installSchemaCallback);
    }

    /**
     * @return string the path to the new schema
     */
    private function updateSchemaFromSharedResource(int $timestamp): string
    {
        $updatePath = $this->definitionsDirectory . '_' . time();
        $this->logger->info("Synchronizing files from shared schema to $updatePath");

        $fs = new FileSystem();
        foreach ($this->sharedSchema->getFiles($timestamp) as $name => $contents) {
            $fs->dumpFile("$updatePath/$name", $contents);
        }

        return $updatePath;
    }
}
