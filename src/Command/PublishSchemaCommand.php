<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Command;

use EzSystems\EzPlatformGraphQL\Schema\Sync\SharedSchema;
use Redis;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class PublishSchemaCommand extends Command
{
    /**
     * @var string
     */
    private $definitionsDirectory;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Sync\SharedSchema
     */
    private $sharedSchema;

    public function __construct(SharedSchema $sharedSchema, string $definitionsDirectory, string $name = null)
    {
        parent::__construct($name);
        $this->definitionsDirectory = $definitionsDirectory;
        $this->sharedSchema = $sharedSchema;
    }

    public function configure()
    {
        $this->setName('ibexa:graphql:publish-schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        foreach ((new Finder())->files()->in($this->definitionsDirectory) as $file) {
            $this->sharedSchema->addFile($file->getBasename(), $file->getContents());
        }

        try {
            $this->sharedSchema->publish(
                filemtime("$this->definitionsDirectory/__classes.map")
            );
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
