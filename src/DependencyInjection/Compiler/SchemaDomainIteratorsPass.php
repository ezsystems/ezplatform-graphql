<?php
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\Schema\Generator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SchemaDomainIteratorsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(Generator::class)) {
            return;
        }

        $generatorDefinition = $container->getDefinition(Generator::class);
        
        $iterators = [];
        foreach ($container->findTaggedServiceIds('ezplatform_graphql.schema_domain_iterator') as $id => $tags) {
            $iterators[] = new Reference($id);
        }

        $generatorDefinition->setArgument('$iterators', $iterators);
        $container->setDefinition(Generator::class, $generatorDefinition);
    }
}