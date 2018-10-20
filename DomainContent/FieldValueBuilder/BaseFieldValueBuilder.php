<?php
namespace BD\EzPlatformGraphQLBundle\DomainContent\FieldValueBuilder;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class BaseFieldValueBuilder implements FieldValueBuilder
{
    private $typesMap = [
        'ezauthor' => 'AuthorFieldValue',
        'ezgmaplocation' => 'MapLocationFieldValue',
        'ezimage' => 'ImageFieldValue',
        'ezrichtext' => 'RichTextFieldValue',
        'ezstring' => 'TextLineFieldValue',
        'ezobjectrelation' => 'RelationFieldValue',
        'ezobjectrelationlist' => 'RelationListFieldValue',
    ];

    public function buildDefinition(FieldDefinition $fieldDefinition)
    {
        return [
            'type' => $this->mapFieldTypeIdentifierToGraphQLType($fieldDefinition->fieldTypeIdentifier),
            'resolve' => sprintf(
                '@=resolver("DomainFieldValue", [value, "%s"])',
                $fieldDefinition->identifier
            ),
        ];
    }

    private function mapFieldTypeIdentifierToGraphQLType($fieldTypeIdentifier)
    {
        return isset($this->typesMap[$fieldTypeIdentifier]) ? $this->typesMap[$fieldTypeIdentifier] : 'GenericFieldValue';
    }
}