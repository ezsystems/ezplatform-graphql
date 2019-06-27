<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Command;

use EzSystems\EzPlatformGraphQL\Schema\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class GeneratePlatformSchemaCommand extends Command
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Generator
     */
    private $generator;

    /**
     * @var string
     */
    private $schemaRootDir;

    public function __construct(Generator $generator, string $schemaRootDir)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->schemaRootDir = $schemaRootDir;
    }

    protected function configure()
    {
        $this
            ->setName('ezplatform:graphql:generate-schema')
            ->setDescription('Generates the GraphQL schema for the eZ Platform instance')
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'Do not write, output the schema only', false)
            ->addOption('include', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Type to output or write', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $this->generator->generate();

        $include = $input->getOption('include');
        $doWrite = $input->getOption('dry-run') === false;

        $fs = new Filesystem();
        foreach ($schema as $type => $definition) {
            if (count($include) && !in_array($type, $include)) {
                continue;
            }
            $typeFilePath = $this->schemaRootDir . "/$type.types.yaml";

            $yaml = Yaml::dump([$type => $definition], 6);
            if ($doWrite) {
                $fs->dumpFile($typeFilePath, $yaml);
                $output->writeln("Written $typeFilePath");
            } else {
                $output->writeln("\n# $type\n$yaml\n");
            }
        }

        $output->writeln('');
        $this->compileTypes($output);
    }

    private function compileTypes(OutputInterface $output)
    {
        $command = $this->getApplication()->find('graphql:compile');
        $command->run(new StringInput('graphql:compile'), $output);
    }
}
