<?php
namespace EzSystems\EzPlatformGraphQL\Command;

use EzSystems\EzPlatformGraphQL\Schema\SchemaGenerator;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated replaced by ezplatform:graphql:generate-schema
 */
class GeneratePlatformDomainTypesCommand extends GeneratePlatformSchemaCommand
{
    public function __construct(Repository $repository, SchemaGenerator $generator)
    {
        parent::__construct($repository, $generator);
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('bd:platform-graphql:generate-domain-schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        @trigger_error(
            "The command name bd:platform-graphql:generate-domain-schema is deprecated. Use ezplatform:graphql:generate-schema instead.",
            E_USER_DEPRECATED
        );

        parent::execute($input, $output);

        $output->writeln(
            '<error>The command name bd:platform-graphql:generate-domain-schema is deprecated. Use ezplatform:graphql:generate-schema instead.</error>'
        );
    }
}