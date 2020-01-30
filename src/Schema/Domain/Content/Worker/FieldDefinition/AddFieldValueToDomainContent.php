<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

class AddFieldValueToDomainContent extends BaseWorker implements Worker
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper
     */
    private $fieldDefinitionMapper;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionArgsBuilderMapper[]
     */
    private $argsMappers;

    public function __construct(FieldDefinitionMapper $fieldDefinitionMapper, array $argsMappers = [])
    {
        $this->fieldDefinitionMapper = $fieldDefinitionMapper;
        $this->argsMappers = $argsMappers;
    }

    public function work(Builder $schema, array $args)
    {
        $definition = $this->getDefinition($args['FieldDefinition']);
        $schema->addFieldToType(
            $this->typeName($args),
            new Input\Field($this->fieldName($args), $definition['type'], $definition)
        );
    }

    private function getDefinition(FieldDefinition $fieldDefinition)
    {
        $definition = [
            'type' => $this->fieldDefinitionMapper->mapToFieldValueType($fieldDefinition),
            'resolve' => $this->fieldDefinitionMapper->mapToFieldValueResolver($fieldDefinition),
        ];

        if (isset($this->argsMappers[$fieldDefinition->fieldTypeIdentifier])) {
            $definition['argsBuilder'] = $this->argsMappers[$fieldDefinition->fieldTypeIdentifier]->mapToFieldValueArgsBuilder($fieldDefinition);
        }

        return $definition;
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            & isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && !$schema->hasTypeWithField($this->typeName($args), $this->fieldName($args));
    }

    protected function typeName(array $args): string
    {
        return $this->getNameHelper()->domainContentName($args['ContentType']);
    }

    protected function fieldName($args): string
    {
        return $this->getNameHelper()->fieldDefinitionField($args['FieldDefinition']);
    }
}
