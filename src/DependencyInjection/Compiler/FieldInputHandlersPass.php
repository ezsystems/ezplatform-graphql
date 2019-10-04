<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\DomainContentMutationResolver;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\ConfigurableFieldDefinitionInputMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition\AddFieldDefinitionToDomainContentMutation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldInputHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DomainContentMutationResolver::class)) {
            return;
        }

        if (!$container->has(AddFieldDefinitionToDomainContentMutation::class)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('ezplatform_graphql.fieldtype_input_handler');

        $handlers = [];
        $typesMapping = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['fieldtype'])) {
                    throw new \InvalidArgumentException(
                        "The ezplatform_graphql.fieldtype_input_handler tag requires a 'fieldtype' property set to the Field Type's identifier"
                    );
                }

                if (!isset($tag['inputType'])) {
                    throw new \InvalidArgumentException(
                        "The ezplatform_graphql.fieldtype_input_handler tag requires an 'inputType' property set to the GraphQL input type it uses"
                    );
                }

                $handlers[$tag['fieldtype']] = new Reference($id);
                $typesMapping[$tag['fieldtype']] = $tag['inputType'];
            }
        }

        $container->findDefinition(DomainContentMutationResolver::class)->setArgument('$fieldInputHandlers', $handlers);
        $container->findDefinition(ConfigurableFieldDefinitionInputMapper::class)->setArgument('$typesMap', $typesMapping);
    }
}
