<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\FieldValueBuilder;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class BaseFieldValueBuilder implements FieldValueBuilder
{
    const DEFAULT_RESOLVER = '@=resolver("DomainFieldValue", [value, "%s"])';

    private $typesMap = [
        'ezauthor' => ["[AuthorFieldValue]", '@=resolver("DomainFieldValue", [value, "%s"]).authors'],
        'ezbinaryfile' => 'BinaryFileFieldValue',
        'ezboolean' => ['Boolean', '@=resolver("DomainFieldValue", [value, "%s"]).bool'],
        'ezcountry' => 'String',
        'ezdate' => ['DateTime', '@=resolver("DomainFieldValue", [value, "%s"]).value'],
        'ezdatetime' => ['DateTime', '@=resolver("DomainFieldValue", [value, "%s"]).value'],
        'ezemail' => 'String',
        'ezfloat' => ['Float', '@=resolver("DomainFieldValue", [value, "%s"]).value'],
        'ezgmaplocation' => 'MapLocationFieldValue',
        'ezimage' => 'ImageFieldValue',
        'ezimageasset' => ['ImageFieldValue', '@=resolver("DomainImageAssetFieldValue", [value, "%s"]).value'],
        'ezinteger' => ['Int', '@=resolver("DomainFieldValue", [value, "%s"]).value'],
        'ezkeyword' => ['[String]', '@=resolver("DomainFieldValue", [value, "%s"]).values'],
        'ezmedia' => 'MediaFieldValue',
        'ezobjectrelation' => 'RelationFieldValue',
        'ezobjectrelationlist' => 'RelationListFieldValue',
        'ezrichtext' => 'RichTextFieldValue',
        'ezstring' => 'String',
        'eztext' => 'String',
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