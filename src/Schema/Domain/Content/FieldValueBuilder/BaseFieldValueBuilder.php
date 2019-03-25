<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class BaseFieldValueBuilder implements FieldValueBuilder
{
    const DEFAULT_RESOLVER = '@=field';

    private $typesMap = [
        'ezauthor' => ["[AuthorFieldValue]", '@=field.authors'],
        'ezbinaryfile' => 'BinaryFileFieldValue',
        'ezboolean' => ['Boolean', '@=field.bool'],
        'ezcountry' => 'String',
        'ezdate' => ['DateTime', '@=field.date'],
        'ezdatetime' => ['DateTime', '@=field.value'],
        'ezemail' => 'String',
        'ezfloat' => ['Float', '@=field.value'],
        'ezgmaplocation' => 'MapLocationFieldValue',
        'ezimage' => 'ImageFieldValue',
        'ezimageasset' => ['ImageFieldValue', '@=resolver("DomainImageAssetFieldValue", [field, content])'],
        'ezinteger' => ['Int', '@=field.value'],
        'ezkeyword' => ['[String]', '@=field.values'],
        'ezmedia' => 'MediaFieldValue',
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