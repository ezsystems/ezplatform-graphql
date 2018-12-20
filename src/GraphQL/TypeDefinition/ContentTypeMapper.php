<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\TypeDefinition;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Maps a Platform Content Type to a Type Definition array.
 */
class ContentTypeMapper
{
    /**
     * FieldType <=> GraphQL type mapping.
     * @todo Deduplicate, this comes from ContentResolver.
     *
     * @var array
     */
    private $typesMap = [
        'ezauthor' => 'AuthorFieldValue',
        'ezgmaplocation' => 'MapLocationFieldValue',
        'ezimage' => 'ImageFieldValue',
        'ezrichtext' => 'RichTextFieldValue',
        'ezstring' => 'TextLineFieldValue',
        'ezobjectrelation' => 'RelationFieldValue',
        'ezobjectrelationlist' => 'RelationListFieldValue',
    ];

    /**
     * @return array A GraphQL type definition.
     */
    public function mapContentType(ContentType $contentType)
    {
        $fields = [
            '_content' => [
                'description' => 'Underlying content item',
                'type' => 'Content',
                'resolve' => '@=value["_content"].contentInfo'
            ],
            '_location' => [
                'description' => 'Main location',
                'type' => 'Location',
                'resolve' => '@=resolver("LocationById", [value["_content"].contentInfo.mainLocationId])'
            ],
            '_allLocations' => [
                'description' => 'All the locations',
                'type' => '[Location]',
                'resolve' => '@=resolver("LocationsByContentId", [value["_content"].id])'
            ]
        ];

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            $descriptions = $fieldDefinition->getDescriptions();

            $fields[$fieldDefinition->identifier] = [
                'type' => $this->mapFieldTypeIdentifierToGraphQLType($fieldDefinition->fieldTypeIdentifier),
                'resolve' => sprintf(
                    '@=resolver("DomainFieldValue", [value, "%s"])',
                    $fieldDefinition->identifier
                ),
            ];

            if (isset($descriptions['eng-GB'])) {
                $fields[$fieldDefinition->identifier]['description'] = $descriptions['eng-GB'];
            }
        }

        return [
            'type' => 'object',
            'config' => [
                'fields' => $fields,
                'interfaces' => ['DomainContent'],
            ]
        ];
    }

    private function mapFieldTypeIdentifierToGraphQLType($fieldTypeIdentifier)
    {
        return isset($this->typesMap[$fieldTypeIdentifier]) ? $this->typesMap[$fieldTypeIdentifier] : 'GenericFieldValue';
    }
}