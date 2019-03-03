<?php

namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\SortBy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SearchSortClauseMappersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(SortBy::class)) {
            return;
        }

        $definition = $container->findDefinition(SortBy::class);
        $sortClauseTaggedServices = $container->findTaggedServiceIds('ezplatform_graphql.query_sortclause_visitor');

        $sortClauseVisitors = [];
        foreach ($sortClauseTaggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['inputKey'])) {
                    $sortClauseVisitors[$tag['inputKey']] = new Reference($id);
                }
            }
        }

        $definition->setArgument('$sortClauseVisitors', $sortClauseVisitors);
    }

}
