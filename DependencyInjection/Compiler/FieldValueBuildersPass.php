<?php
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\FieldDefinition\AddFieldDefinitionToDomainContent;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FieldValueBuildersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(AddFieldDefinitionToDomainContent::class)) {
            return;
        }

        $definition = $container->findDefinition(AddFieldDefinitionToDomainContent::class);
        $taggedServices = $container->findTaggedServiceIds('ezplatform_graphql.field_value_builder');

        $builders = [];
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \InvalidArgumentException(
                        "The ezplatform_graphql.field_value_builder tag requires a 'type' property set to the Field Type's identifier"
                    );
                }

                $builders[$tag['type']] = new Reference($id);
            }
        }

        $definition->setArgument('$fieldValueBuilders', $builders);
    }
}