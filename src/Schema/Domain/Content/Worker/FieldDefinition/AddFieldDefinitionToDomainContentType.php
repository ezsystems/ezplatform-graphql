<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\Repository\Values\MultiLanguageDescription;

class AddFieldDefinitionToDomainContentType extends BaseWorker implements Worker
{
    const TYPES_MAP = [
        'ezbinaryfile' => 'BinaryFieldDefinition',
        'ezboolean' => 'CheckboxFieldDefinition',
        'ezcountry' => 'CountryFieldDefinition',
        'ezmediafile' => 'CheckboxFieldDefinition',
        'ezfloat' => 'FloatFieldDefinition',
        'ezimage' => 'BinaryFieldDefinition',
        'ezinteger' => 'IntegerFieldDefinition',
        'ezmedia' => 'MediaFieldDefinition',
        'ezobjectrelation' => 'RelationFieldDefinition',
        'ezobjectrelationlist' => 'RelationListFieldDefinition',
        'ezstring' => 'TextLineFieldDefinition',
        'ezselection' => 'SelectionFieldDefinition',
        'eztext' => 'TextBlockFieldDefinition',
    ];

    public function work(Builder $schema, array $args)
    {
        $schema->addFieldToType($this->typeName($args), new Input\Field(
            $this->fieldName($args),
            $this->fieldType($args),
            [
                'description' => $this->fieldDescription($args),
                'resolve' => sprintf(
                    '@=value.getFieldDefinition("%s")',
                    $args['FieldDefinition']->identifier
                ),
            ]
        ));
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

    /**
     * @param array $args
     * @return string
     */
    protected function typeName(array $args): string
    {
        return $this->getNameHelper()->domainContentTypeName($args['ContentType']);
    }

    protected function fieldName($args): string
    {
        return $this->getNameHelper()->fieldDefinitionField($args['FieldDefinition']);
    }

    public function fieldDescription($args)
    {
        $description = '';
        if ($args['FieldDefinition'] instanceof MultiLanguageDescription) {
            $description = $args['FieldDefinition']->getDescription('eng-GB') ?? '';
        }

        return $description;
    }

    private function fieldType($args)
    {
        $fieldDefinition = $args['FieldDefinition'];

        return isset(self::TYPES_MAP[$fieldDefinition->fieldTypeIdentifier])
            ? self::TYPES_MAP[$fieldDefinition->fieldTypeIdentifier]
            : 'FieldDefinition';
    }
}