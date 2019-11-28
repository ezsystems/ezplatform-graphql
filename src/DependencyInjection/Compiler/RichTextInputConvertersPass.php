<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldType\RichText as RichTextInputHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RichTextInputConvertersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(RichTextInputHandler::class)) {
            return;
        }

        $definition = $container->findDefinition(RichTextInputHandler::class);
        $taggedServices = $container->findTaggedServiceIds('ezplatform_graphql.richtext_input_converter');

        $handlers = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['format'])) {
                    throw new \InvalidArgumentException("The ezplatform_graphql.richtext_input_converter tag requires a 'format' property set to the converted format");
                }

                $handlers[$tag['format']] = new Reference($id);
            }
        }

        $definition->setArgument('$inputConverters', $handlers);
    }
}
