<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\ConfigurableFieldDefinitionMapper;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use PhpSpec\ObjectBehavior;

class ConfigurableFieldDefinitionMapperSpec extends ObjectBehavior
{
    const FIELD_IDENTIFIER = 'test';
    const CONFIG = [
        'configured_type' => [
            'value_type' => self::VALUE_TYPE,
            'definition_type' => self::DEFINITION_TYPE,
            'value_resolver' => self::VALUE_RESOLVER,
        ]
    ];

    const VALUE_TYPE = 'ConfiguredFieldValue';
    const VALUE_RESOLVER = 'valueResolver';
    const DEFINITION_TYPE = 'ConfiguredFieldDefinition';

    function let(FieldDefinitionMapper $innerMapper)
    {
        $this->beConstructedWith($innerMapper, self::CONFIG);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigurableFieldDefinitionMapper::class);
        $this->shouldHaveType(FieldDefinitionMapper::class);
    }

    function it_calls_the_inner_mapper_if_it_does_not_have_a_value_type_for_a_field_type_identifier(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createUnconfiguredFieldDefinition();

        $this->mapToFieldValueType($fieldDefinition)->shouldBeNull();
        $innerMapper->mapToFieldValueType($fieldDefinition)->shouldHaveBeenCalled();
    }

    function it_returns_the_value_type_for_a_configured_field_type_identifier(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createConfiguredFieldDefinition();

        $this->mapToFieldValueType($fieldDefinition)->shouldReturn(self::VALUE_TYPE);
        $innerMapper->mapToFieldValueType($fieldDefinition)->shouldNotHaveBeenCalled();
    }

    function it_calls_the_inner_mapper_if_it_does_not_have_a_definition_type_for_a_field_type_identifier(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createUnconfiguredFieldDefinition();

        $this->mapToFieldDefinitionType($fieldDefinition)->shouldBeNull();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->shouldHaveBeenCalled();
    }

    function it_returns_the_definition_type_for_a_configured_field_type_identifier(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createConfiguredFieldDefinition();

        $this->mapToFieldDefinitionType($fieldDefinition)->shouldReturn(self::DEFINITION_TYPE);
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->shouldNotHaveBeenCalled();
    }

    function it_calls_the_inner_mapper_if_it_does_not_have_a_value_resolver_for_a_field_type_identifier(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createUnconfiguredFieldDefinition();

        $this->mapToFieldDefinitionType($fieldDefinition)->shouldBeNull();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->shouldHaveBeenCalled();
    }

    function it_returns_the_completed_value_resolver_for_a_configured_field_type_identifier(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->createConfiguredFieldDefinition();

        $this->mapToFieldValueResolver($fieldDefinition)->shouldReturn('@=' . self::VALUE_RESOLVER);
        $innerMapper->mapToFieldValueResolver($fieldDefinition)->shouldNotHaveBeenCalled();
    }

    /**
     * @return FieldDefinition
     */
    protected function createConfiguredFieldDefinition(): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => 'test',
            'fieldTypeIdentifier' => 'configured_type',
        ]);
    }

    /**
     * @return FieldDefinition
     */
    protected function createUnconfiguredFieldDefinition(): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => 'test',
            'fieldTypeIdentifier' => 'unconfigured_type',
        ]);
    }
}
