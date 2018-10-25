<?php
namespace BD\EzPlatformGraphQLBundle\DomainContent\FieldValueBuilder;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class BaseFieldValueBuilder implements FieldValueBuilder
{
    const DEFAULT_RESOLVER = '@=resolver("DomainFieldValue", [value, "%s"])';

    private $typesMap = [
        'ezauthor' => 'AuthorFieldValue',
        'ezgmaplocation' => 'MapLocationFieldValue',
        'ezimage' => 'ImageFieldValue',
        'ezrichtext' => 'RichTextFieldValue',
        'ezfloat' => ['Float', '@=resolver("DomainFieldValue", [value, "%s"]).value'],
        'ezinteger' => ['Int', '@=resolver("DomainFieldValue", [value, "%s"]).value'],
        'ezboolean' => ['Boolean', '@=resolver("DomainFieldValue", [value, "%s"]).bool'],
        'ezstring' => ['String', '@=resolver("DomainFieldValue", [value, "%s"]).text'],
        'eztext' => ['String', '@=resolver("DomainFieldValue", [value, "%s"]).text'],
        'ezobjectrelation' => 'RelationFieldValue',
        'ezobjectrelationlist' => 'RelationListFieldValue',
    ];

    public function buildDefinition(FieldDefinition $fieldDefinition)
    {
        $mapping = $this->mapFieldTypeIdentifierToGraphQLType($fieldDefinition->fieldTypeIdentifier);

        if (is_array($mapping)) {
            list($type, $resolver) = $mapping;
        } else {
            $type = $mapping;
            $resolver = self::DEFAULT_RESOLVER;
        }

        return [
            'type' => $type,
            'resolve' => sprintf($resolver, $fieldDefinition->identifier),
        ];
    }

    private function mapFieldTypeIdentifierToGraphQLType($fieldTypeIdentifier)
    {
        return isset($this->typesMap[$fieldTypeIdentifier]) ? $this->typesMap[$fieldTypeIdentifier] : 'GenericFieldValue';
    }
}