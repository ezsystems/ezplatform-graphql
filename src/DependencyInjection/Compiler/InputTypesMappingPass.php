<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\DependencyInjection\Compiler;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\ConfigurableFieldDefinitionMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Processes the deprecated inputType attribute of the ezplatform_graphql.fieldtype_input_handler service tag.
 *
 * The value is added to the ezplatform_graphql.schema.content.mapping.field_definition_type as the input_type
 * configuration key.
 *
 * Since the FieldDefinitionMapper chain doesn't expose mapToFieldValueInput type yet,
 * the ConfigurableFieldDefinitionMapper is then tagged for each fieldtype configured with input_type so that
 * it is used by AddFieldDefinitionToDomainContentMutation.
 *
 * @deprecated will be removed in ezplatform-graphql 2.0.
 */
final class InputTypesMappingPass implements CompilerPassInterface
{
    private const INPUT_HANDLER_TAG = 'ezplatform_graphql.fieldtype_input_handler';
    private const INPUT_MAPPER_TAG = 'ezplatform_graphql.field_definition_input_mapper';
    private const PARAM = 'ezplatform_graphql.schema.content.mapping.field_definition_type';

    public function process(ContainerBuilder $container)
    {
        $mappingConfiguration = $container->getParameter(self::PARAM);
        foreach ($container->findTaggedServiceIds(self::INPUT_HANDLER_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['fieldtype'])) {
                    throw new \InvalidArgumentException(sprintf("The %s tag requires a 'fieldtype' property set to the Field Type's identifier", self::INPUT_HANDLER_TAG));
                }

                if (isset($tag['inputType'])) {
                    @trigger_error(
                        sprintf(
                            "The 'inputType' service tag attribute on %s is deprecated, and won't work anymore in ezplatform-graphql 2.0. Use the %s container parameter.",
                            $id,
                            self::PARAM
                        ),

                        E_USER_DEPRECATED
                    );
                    $mappingConfiguration[$tag['fieldtype']]['input_type'] = $tag['inputType'];
                }
            }
        }

        $container->setParameter(self::PARAM, $mappingConfiguration);

        $configurableMapperDefinition = $container->getDefinition(ConfigurableFieldDefinitionMapper::class);
        foreach ($mappingConfiguration as $fieldtype => $configuration) {
            if (isset($configuration['input_type'])) {
                $configurableMapperDefinition->addTag(self::INPUT_MAPPER_TAG, ['fieldtype' => $fieldtype]);
            }
        }
    }
}
