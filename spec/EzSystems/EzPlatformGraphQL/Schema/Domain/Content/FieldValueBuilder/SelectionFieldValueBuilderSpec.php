<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\SelectionFieldValueBuilder;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SelectionFieldValueBuilderSpec extends ObjectBehavior
{
    const FIELD_IDENTIFIER = 'test';
    const TYPE_IDENTIFIER = 'ezselection';

    function it_is_initializable()
    {
        $this->shouldHaveType(SelectionFieldValueBuilder::class);
    }

    function it_maps_to_an_array_of_strings_if_multiple_is_set_to_true()
    {
        $fieldDefinition = $this->createMultiFieldDefinition();

        $this->buildDefinition($fieldDefinition)->shouldBeGraphQLArrayOfStrings();
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithTheSelectionFieldValueResolver();
    }

    function it_maps_to_a_string_if_multiple_is_set_to_false()
    {
        $fieldDefinition = $this->createSingleFieldDefinition();

        $this->buildDefinition($fieldDefinition)->shouldBeGraphQLString();
        $this->buildDefinition($fieldDefinition)->shouldBeResolvedWithTheSelectionFieldValueResolver();
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

    public function getMatchers(): array
    {
        return [
            'beGraphQLString' => function($definition) {
                return $definition['type'] === 'String';
            },
            'beGraphQLArrayOfStrings' => function($definition) {
                return $definition['type'] === '[String]';
            },
            'beResolvedWithTheSelectionFieldValueResolver' => function($definition) {
                return strpos($definition['resolve'], 'resolver("SelectionFieldValue"') !== false;
            }
        ];
    }


}
