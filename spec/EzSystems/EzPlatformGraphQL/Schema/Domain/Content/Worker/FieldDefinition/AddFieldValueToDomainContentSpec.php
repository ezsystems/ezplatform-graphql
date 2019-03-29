<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\FieldValueBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition\AddFieldValueToDomainContent;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgument;
use eZ\Publish\Core\Repository\Values\ContentType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddFieldValueToDomainContentSpec extends ObjectBehavior
{
    const FIELDTYPE_IDENTIFIER = 'field';
    const FIELD_IDENTIFIER = 'test';

    function let(
        NameHelper $nameHelper,
        FieldDefinitionMapper $mapper
    )
    {
        $this->beConstructedWith($mapper);
        $this->setNameHelper($nameHelper);

        $nameHelper->domainContentName(Argument::any())->willReturn('TestContent');
        $nameHelper->fieldDefinitionField(Argument::any())->willReturn('test');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddFieldValueToDomainContent::class);
    }
    
    function it_adds_to_the_schema_what_was_returned_by_the_builder(
        Builder $schema,
        FieldDefinitionMapper $mapper
    )
    {
        $mapper->mapToFieldValueType(Argument::any())->willReturn('String');
        $mapper->mapToFieldValueResolver(Argument::any())->willReturn('field');

        $schema->addFieldToType(
            Argument::any(),
            Argument::allOf(
                FieldArgument::hasName(self::FIELD_IDENTIFIER),
                FieldArgument::hasType('String'),
                FieldArgument::withResolver('field')
            )
        );
        $this->work($schema, $this->buildArguments());
    }

    private function buildArguments($fieldTypeIdentifier = self::FIELDTYPE_IDENTIFIER)
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
