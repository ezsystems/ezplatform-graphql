<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 23/09/2018
 * Time: 23:45
 */

namespace BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\FieldDefinition;

use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\BaseWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class AddFieldDefinitionToDomainContent extends BaseWorker implements SchemaWorker
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

    public function work(array &$schema, array $args)
    {
        $fieldDefinition = $args['FieldDefinition'];
        $fieldDefinitionField = $this->getFieldDefinitionField($args['FieldDefinition']);
        $domainContentName = $this->getDomainContentName($args['ContentType']);

        $schema[$domainContentName]['config']['fields'][$fieldDefinitionField] = [
            'type' => $this->mapFieldTypeIdentifierToGraphQLType($fieldDefinition->fieldTypeIdentifier),
            'resolve' => sprintf(
                '@=resolver("DomainFieldValue", [value, "%s"])',
                $fieldDefinition->identifier
            ),
        ];

        $descriptions = $fieldDefinition->getDescriptions();
        if (isset($descriptions['eng-GB'])) {
            $fields[$fieldDefinitionField]['description'] = $descriptions['eng-GB'];
        }
    }

    private function mapFieldTypeIdentifierToGraphQLType($fieldTypeIdentifier)
    {
        return isset($this->typesMap[$fieldTypeIdentifier]) ? $this->typesMap[$fieldTypeIdentifier] : 'GenericFieldValue';
    }


    public function canWork(array $schema, array $args)
    {
        return
            isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            & isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && !$this->isFieldDefined($schema, $args);
    }

    /**
     * @param ContentType $contentType
     * @return string
     */
    protected function getDomainContentName(ContentType $contentType): string
    {
        return $this->getNameHelper()->domainContentName($contentType);
    }

    /**
     * @param FieldDefinition $fieldDefinition
     * @return string
     */
    protected function getFieldDefinitionField(FieldDefinition $fieldDefinition): string
    {
        return $this->getNameHelper()->fieldDefinitionField($fieldDefinition);
    }

    private function isFieldDefined($schema, $args)
    {
        return isset(
            $schema[$this->getDomainContentName($args['ContentType'])]
                   ['config']['fields']
                   [$this->getFieldDefinitionField($args['FieldDefinition'])]);
    }
}