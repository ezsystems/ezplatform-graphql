<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\FieldValueBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;


class AddFieldValueToDomainContent extends BaseWorker implements Worker
{
    /**
     * @var FieldValueBuilder[]
     */
    private $fieldValueBuilders;

    /**
     * @var FieldValueBuilder
     */
    private $defaultFieldValueBuilder;

    public function __construct(FieldValueBuilder $defaultFieldValueBuilder, array $fieldValueBuilders = [])
    {
        $this->fieldValueBuilders = $fieldValueBuilders;
        $this->defaultFieldValueBuilder = $defaultFieldValueBuilder;
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
        return isset($this->fieldValueBuilders[$fieldDefinition->fieldTypeIdentifier])
            ? $this->fieldValueBuilders[$fieldDefinition->fieldTypeIdentifier]->buildDefinition($fieldDefinition)
            : $this->defaultFieldValueBuilder->buildDefinition($fieldDefinition);
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