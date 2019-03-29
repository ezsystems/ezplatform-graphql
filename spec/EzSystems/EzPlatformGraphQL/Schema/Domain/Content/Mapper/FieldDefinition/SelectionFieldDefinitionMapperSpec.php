<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\SelectionFieldDefinitionMapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SelectionFieldDefinitionMapperSpec extends ObjectBehavior
{
    const FIELD_IDENTIFIER = 'test';
    const TYPE_IDENTIFIER = 'ezselection';

    function let(FieldDefinitionMapper $innerMapper)
    {
        $this->beConstructedWith($innerMapper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectionFieldDefinitionMapper::class);
        $this->shouldHaveType(FieldDefinitionMapper::class);
    }

    function it_maps_to_an_array_of_strings_if_multiple_is_set_to_true()
    {
        $this->mapToFieldValueType($this->createMultiFieldDefinition())->shouldReturn('[String]');
    }

    function it_maps_to_a_string_if_multiple_is_set_to_false()
    {
        $this->mapToFieldValueType($this->createSingleFieldDefinition())->shouldReturn('String');
    }

    private function createSingleFieldDefinition()
    {
        return $this->createFieldDefinition(['isMultiple' => false]);
    }

    private function createMultiFieldDefinition()
    {
        return $this->createFieldDefinition(['isMultiple' => true]);
    }

    private function createFieldDefinition($options)
    {
        return new FieldDefinition([
            'identifier' => self::FIELD_IDENTIFIER,
            'fieldTypeIdentifier' => self::TYPE_IDENTIFIER,
            'fieldSettings' => [
                'isMultiple' => $options['isMultiple'] ?? false
            ]
        ]);
    }
}
