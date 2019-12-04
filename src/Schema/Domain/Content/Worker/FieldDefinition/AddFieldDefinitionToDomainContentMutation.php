<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

class AddFieldDefinitionToDomainContentMutation extends BaseWorker implements Worker
{
    const OPERATION_CREATE = 'create';
    const OPERATION_UPDATE = 'update';

    /**
     * Mapping of fieldtypes identifiers to their GraphQL input type.
     *
     * @var string[]
     */
    private $typesInputMap;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionInputMapper[]
     */
    private $mappers;

    /**
     * @param string[] $typesInputMap
     * @param \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionInputMapper[] $mappers
     */
    public function __construct(array $typesInputMap = [], array $mappers = [])
    {
        $this->typesInputMap = $typesInputMap;
        $this->mappers = $mappers;
    }

    public function work(Builder $schema, array $args)
    {
        $properties = ['description' => $this->mapDescription($args)];

        $schema->addFieldToType(
            $this->nameCreateInputType($args),
            new Builder\Input\Field(
                $this->nameFieldDefinitionField($args),
                $this->nameFieldType($args, self::OPERATION_CREATE),
                $properties
            )
        );

        $schema->addFieldToType(
            $this->nameUpdateInputType($args),
            new Builder\Input\Field(
                $this->nameFieldDefinitionField($args),
                $this->nameFieldType($args, self::OPERATION_UPDATE),
                $properties
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
            && !$schema->hasTypeWithField($this->nameCreateInputType($args), $this->nameFieldDefinitionField($args));
    }

    protected function nameCreateInputType(array $args): string
    {
        return $this->getNameHelper()->domainContentCreateInputName($args['ContentType']);
    }

    private function nameUpdateInputType(array $args): string
    {
        return $this->getNameHelper()->domainContentUpdateInputName($args['ContentType']);
    }

    protected function nameFieldDefinitionField(array $args): string
    {
        return $this->getNameHelper()->fieldDefinitionField($args['FieldDefinition']);
    }

    private function nameFieldType(array $args, $operation): string
    {
        $fieldDefinition = $args['FieldDefinition'];
        $contentType = $args['ContentType'];

        if (isset($this->mappers[$fieldDefinition->fieldTypeIdentifier])) {
            $type = $this->mappers[$fieldDefinition->fieldTypeIdentifier]->mapToFieldValueInputType($contentType, $fieldDefinition);
        } elseif (isset($this->typesInputMap[$fieldDefinition->fieldTypeIdentifier])) {
            $type = $this->typesInputMap[$fieldDefinition->fieldTypeIdentifier];
        } else {
            $type = 'String';
        }

        $requiredFlag = $operation == self::OPERATION_CREATE && $fieldDefinition->isRequired ? '!' : '';

        return $type . $requiredFlag;
    }

    /**
     * Extracts the description of a field definition.
     *
     * @param array $args
     */
    private function mapDescription($args): ?string
    {
        return $args['FieldDefinition']->getDescription($args['ContentType']->mainLanguageCode);
    }
}
