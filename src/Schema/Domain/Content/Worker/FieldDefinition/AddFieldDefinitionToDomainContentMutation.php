<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionInputMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

class AddFieldDefinitionToDomainContentMutation extends BaseWorker implements Worker
{
    const OPERATION_CREATE = 'create';
    const OPERATION_UPDATE = 'update';

    /**
     * Mapping of fieldtypes identifiers to their GraphQL input type.
     *
     * @var FieldDefinitionInputMapper
     */
    private $mapper;

    public function __construct(FieldDefinitionInputMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function work(Builder $schema, array $args)
    {
        $fieldDefinition = $args['FieldDefinition'];
        $contentType = $args['ContentType'];

        $fieldDefinitionField = $this->getFieldDefinitionField($fieldDefinition);

        $schema->addFieldToType(
            $this->getCreateInputName($contentType),
            new Builder\Input\Field(
                $fieldDefinitionField,
                $this->fieldType($args, self::OPERATION_CREATE),
                ['description' => $fieldDefinition->getDescriptions()['eng-GB'] ?? '']
            )
        );

        $schema->addFieldToType(
            $this->getUpdateInputName($contentType),
            new Builder\Input\Field(
                $fieldDefinitionField,
                $this->fieldType($args, self::OPERATION_UPDATE),
                ['description' => $fieldDefinition->getDescriptions()['eng-GB'] ?? '']
            )
        );
    }

    public function canWork(Builder $schema, array $args): bool
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            && !$schema->hasTypeWithField($this->getCreateInputName($args['ContentType']), $this->getFieldDefinitionField($args['FieldDefinition']));
    }

    /**
     * @param ContentType $contentType
     *
     * @return string
     */
    protected function getCreateInputName(ContentType $contentType): string
    {
        return $this->getNameHelper()->domainContentCreateInputName($contentType);
    }

    /**
     * @param ContentType $contentType
     *
     * @return string
     */
    private function getUpdateInputName($contentType): string
    {
        return $this->getNameHelper()->domainContentUpdateInputName($contentType);
    }

    /**
     * @param FieldDefinition $fieldDefinition
     *
     * @return string
     */
    protected function getFieldDefinitionField(FieldDefinition $fieldDefinition): string
    {
        return $this->getNameHelper()->fieldDefinitionField($fieldDefinition);
    }

    private function fieldType(array $args, $operation): string
    {
        if (!isset($args['FieldDefinition']) || !$args['FieldDefinition'] instanceof FieldDefinition) {
            throw new \InvalidArgumentException("Missing FieldDefinition argument");
        } else {
            $fieldDefinition = $args['FieldDefinition'];
        }

        if (!isset($args['ContentType']) || !$args['ContentType'] instanceof ContentType) {
            throw new \InvalidArgumentException("Missing ContentType argument");
        } else {
            $contentType = $args['ContentType'];
        }

        return sprintf(
            '%s%s',
            $this->mapper->mapToFieldValueInputType($contentType, $fieldDefinition),
            $operation == self::OPERATION_CREATE && $fieldDefinition->isRequired ? '!' : ''
        );
    }
}
