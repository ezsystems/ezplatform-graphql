<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\Command;

use BD\EzPlatformGraphQLBundle\Schema\SchemaGenerator;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class GeneratePlatformDomainTypesCommand extends Command
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \BD\EzPlatformGraphQLBundle\Schema\SchemaGenerator
     */
    private $generator;

    const TYPES_DIRECTORY = "src/AppBundle/Resources/config/graphql";

    public function __construct(Repository $repository, SchemaGenerator $generator)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->generator = $generator;
    }

    protected function configure()
    {
        $this->setName('bd:platform-graphql:generate-domain-schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $this->generator->generate();

        $fs = new Filesystem();
        foreach ($schema as $type => $definition) {
            $typeFilePath = self::TYPES_DIRECTORY . "/$type.types.yml";
            $fs->dumpFile(
                $typeFilePath,
                Yaml::dump([$type => $definition], 6)
            );
            $output->writeln("Written $typeFilePath");
        }
    }
}
