<?php

namespace EzSystems\EzPlatformGraphQL;

use EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;
use EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL\YamlSchemaProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzSystemsEzPlatformGraphQLBundle extends Bundle
{
    const CONFIG_DIR = __DIR__ . '/../../../../app/config/graphql';
    const EZPLATFORM_CONFIG_DIR = self::CONFIG_DIR . 'ezplatform';
    const FIELDS_DEFINITION_FILE = __DIR__ . '/Resources/config/graphql/Field.types.yml';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\FieldValueTypesPass(self::FIELDS_DEFINITION_FILE));
        $container->addCompilerPass(new Compiler\FieldValueBuildersPass());
        $container->addCompilerPass(new Compiler\SchemaWorkersPass());
        $container->addCompilerPass(new Compiler\SchemaDomainIteratorsPass());
    }

    protected function createContainerExtension()
    {
        if (class_exists($class = $this->getContainerExtensionClass())) {
            return new $class(new YamlSchemaProvider(self::CONFIG_DIR));
        }
    }
}
