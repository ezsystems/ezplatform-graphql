<?php
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SchemaBuildersPass implements CompilerPassInterface
{
    const ID = 'EzSystems\EzPlatformGraphQL\Schema\SchemaGenerator';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::ID)) {
            return;
        }

        $generatorDefinition = $container->getDefinition(self::ID);
        
        $workers = [];
        foreach ($container->findTaggedServiceIds('ezplatform_graphql.schema_builder') as $id => $tags) {
            $workers[] = new Reference($id);
        }

        $generatorDefinition->setArgument('$schemaBuilders', $workers);
        $container->setDefinition(self::ID, $generatorDefinition);
    }
}