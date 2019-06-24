<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * @internal
 */
class FieldDefinitionResolver
{
    public function resolveFieldDefinitionName(FieldDefinition $fieldDefinition, $args)
    {
        $languageCode = isset($args['language']) ? $args['language'] : null;

        return $fieldDefinition->getName($languageCode);
    }

    public function resolveFieldDefinitionDescription(FieldDefinition $fieldDefinition, $args)
    {
        $languageCode = isset($args['language']) ? $args['language'] : null;

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
