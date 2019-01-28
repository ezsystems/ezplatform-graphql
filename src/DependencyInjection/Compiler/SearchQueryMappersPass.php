<?php

namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use BD\EzPlatformGraphQLBundle\GraphQL\InputMapper\SearchQueryMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SearchQueryMappersPass implements CompilerPassInterface
{
    const ID = 'BD\EzPlatformGraphQLBundle\GraphQL\InputMapper\SearchQueryMapper';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::ID)) {
            return;
        }

        $definition = $container->findDefinition(SearchQueryMapper::class);
        $taggedServices = $container->findTaggedServiceIds('ezplatform_graphql.search_query_mapper');

        $criteriaMappers = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['inputKey'])) {
                    $criteriaMappers[$tag['inputKey']] = new Reference($id);
                }
            }
        }

        $definition->setArgument('$criteriaMappers', $criteriaMappers);
    }
}
