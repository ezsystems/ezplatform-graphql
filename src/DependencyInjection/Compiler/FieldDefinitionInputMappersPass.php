<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition\AddFieldDefinitionToDomainContentMutation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldDefinitionInputMappersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $mappers = [];
        foreach ($container->findTaggedServiceIds('ezplatform_graphql.field_definition_input_mapper') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['fieldtype'])) {
                    throw new \InvalidArgumentException("The ezplatform_graphql.field_definition_input_mapper tag requires a 'fieldtype' attribute set to the Field Type's identifier");
                }
                $mappers[$tag['fieldtype']] = new Reference($id);
            }
        }

        if (empty($mappers)) {
            return;
        }

        $definition = $container->getDefinition(AddFieldDefinitionToDomainContentMutation::class);
        $definition->setArgument('$mappers', $mappers);
        $container->setDefinition(AddFieldDefinitionToDomainContentMutation::class, $definition);
    }
}
