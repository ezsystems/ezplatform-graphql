<?php

namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SearchQueryMappersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::ID)) {
            return;
        }

        $definition = $container->findDefinition(SearchQueryMapper::class);
        $taggedServices = $container->findTaggedServiceIds('ezplatform_graphql.query_input_visitor');

        $queryInputVisitors = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['inputKey'])) {
                    $queryInputVisitors[$tag['inputKey']] = new Reference($id);
                }
            }
        }

        $definition->setArgument('$queryInputVisitors', $queryInputVisitors);
    }
}
