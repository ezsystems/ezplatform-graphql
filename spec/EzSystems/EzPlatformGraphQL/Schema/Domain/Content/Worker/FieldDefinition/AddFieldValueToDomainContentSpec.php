<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\FieldValueBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition\AddFieldValueToDomainContent;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgument;
use eZ\Publish\Core\Repository\Values\ContentType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddFieldValueToDomainContentSpec extends ObjectBehavior
{
    const FIELDTYPE_WITH_BUILDER = 'withbuilder';
    const FIELDTYPE_WITHOUT_BUILDER = 'withoutbuilder';
    const FIELD_IDENTIFIER = 'test';

    function let(
        NameHelper $nameHelper,
        FieldValueBuilder $defaultBuilder,
        FieldValueBuilder $otherBuilder
    )
    {
        $this->beConstructedWith($defaultBuilder, [self::FIELDTYPE_WITH_BUILDER => $otherBuilder]);
        $this->setNameHelper($nameHelper);

        $nameHelper->domainContentName(Argument::any())->willReturn('TestContent');
        $nameHelper->fieldDefinitionField(Argument::any())->willReturn('test');

        $otherBuilder->buildDefinition(Argument::any())->shouldNotBeCalled();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddFieldValueToDomainContent::class);
    }
    
    function it_uses_the_default_field_value_builder_if_the_field_type_does_not_have_a_builder(
        Builder $schema,
        FieldValueBuilder $defaultBuilder,
        FieldValueBUilder $otherBuilder
    )
    {
        $defaultBuilder->buildDefinition(Argument::any())->willReturn(['name' => self::FIELD_IDENTIFIER, 'type' => 'String']);
        $otherBuilder->buildDefinition(Argument::any())->shouldNotBeCalled();
        $this->work($schema, $this->buildArguments(self::FIELDTYPE_WITHOUT_BUILDER));
    }

    function it_uses_another_field_value_builder_if_the_field_type_has_one(
        Builder $schema,
        FieldValueBuilder $defaultBuilder,
        FieldValueBUilder $otherBuilder
    )
    {
        $defaultBuilder->buildDefinition(Argument::any())->shouldNotBeCalled();
        $otherBuilder->buildDefinition(Argument::any())->shouldBeCalled()->willReturn(['name' => self::FIELD_IDENTIFIER, 'type' => 'String']);
        $this->work($schema, $this->buildArguments(self::FIELDTYPE_WITH_BUILDER));
    }

    function it_adds_to_the_schema_what_was_returned_by_the_builder(
        Builder $schema,
        FieldValueBuilder $defaultBuilder
    )
    {
        $defaultBuilder->buildDefinition(Argument::any())->willReturn([
            'name' => self::FIELD_IDENTIFIER,
            'type' => 'SomeFieldValue',
            'description' => 'The description',
            'resolve' => 'someresolvestring',
        ]);

        $schema->addFieldToType(
            Argument::any(),
            Argument::allOf(
                FieldArgument::hasName(self::FIELD_IDENTIFIER)
            )
        );
        $this->work($schema, $this->buildArguments());
    }

    private function buildArguments($fieldTypeIdentifier = self::FIELDTYPE_WITHOUT_BUILDER)
    {
        return [
            'ContentTypeGroup' => new ContentType\ContentTypeGroup(),
            'ContentType' => new ContentType\ContentType(),
            'FieldDefinition' => new ContentType\FieldDefinition([
                'fieldTypeIdentifier' => $fieldTypeIdentifier
            ]),
        ];
    }
}
