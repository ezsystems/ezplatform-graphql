<?php
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SchemaWorkersPass implements CompilerPassInterface
{
    const ID = 'EzSystems\EzPlatformGraphQL\DomainContent\DomainContentSchemaBuilder';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::ID)) {
            return;
        }

        $generatorDefinition = $container->getDefinition(self::ID);
        
        $workers = [];
        foreach ($container->findTaggedServiceIds('ezplatform_graphql.domain_schema_worker') as $id => $tags) {
            $workers[] = new Reference($id);
        }

        $generatorDefinition->setArgument('$workers', $workers);
        $container->setDefinition(self::ID, $generatorDefinition);
    }
}