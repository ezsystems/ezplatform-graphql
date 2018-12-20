<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class FieldDefinitionResolver
{
    public function resolveFieldDefinitionName(FieldDefinition $fieldDefinition, $args)
    {
        $languageCode = isset($args['languageCode']) ? $args['languageCode'] : null;

        return $fieldDefinition->getName($languageCode);
    }

    public function resolveFieldDefinitionDescription(FieldDefinition $fieldDefinition, $args)
    {
        $languageCode = isset($args['languageCode']) ? $args['languageCode'] : null;

        return $fieldDefinition->getDescription($languageCode);
    }

    public function resolveSelectionFieldDefinitionOptions(array $options)
    {
        $return = [];

        foreach ($options as $index => $label) {
            $return[] = ['index' => $index, 'label' => $label];
        }

        return $return;
    }
}