<?php
namespace BD\EzPlatformGraphQLBundle\DomainContent\FieldValueBuilder;

use BD\EzPlatformGraphQLBundle\DomainContent\NameHelper;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class SelectionFieldValueBuilder implements FieldValueBuilder
{
    public function buildDefinition(FieldDefinition $fieldDefinition)
    {
        $settings = $fieldDefinition->getFieldSettings();

        return [
            'type' => $settings['isMultiple'] ? '[String]' : 'String',
            'resolve' => sprintf(
                '@=resolver("SelectionFieldValue", [value, "%s"])',
                $fieldDefinition->identifier
            )
        ];
    }
}