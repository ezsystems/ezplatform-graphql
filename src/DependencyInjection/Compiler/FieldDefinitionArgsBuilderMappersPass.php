<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition\AddFieldValueToDomainContent;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Handles the ezplatform_graphql.field_definition_args_builder_mapper service tag.
 * Injects tagged services as the $argsMapper argument of the AddFieldValueToDomainContent Field Definition Worker.
 *
 * @deprecated deprecated since 1.0, will be removed in 1.0. Use the FieldDefinitionArgsBuilderMapper interface in your decorating mapper.
 */
class FieldDefinitionArgsBuilderMappersPass implements CompilerPassInterface
{
    private const TAG = 'ezplatform_graphql.field_definition_args_builder_mapper';
    const ATTRIBUTE = 'fieldtype';

    public function process(ContainerBuilder $container)
    {
        $mappers = [];
        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['fieldtype'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            "The %s tag requires a '%s' attribute set to the Field Type's identifier",
                            self::TAG,
                            self::ATTRIBUTE
                        )
                    );
                }
                $mappers[$tag[self::ATTRIBUTE]] = new Reference($id);
            }
        }

        if (empty($mappers)) {
            return;
        }

        $definition = $container->getDefinition(AddFieldValueToDomainContent::class);
        $definition->setArgument('$argsMappers', $mappers);
        $container->setDefinition(AddFieldValueToDomainContent::class, $definition);
    }
}
