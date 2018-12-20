<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition\AddFieldDefinitionToDomainContentType;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgument;
use eZ\Publish\Core\Repository\Values\ContentType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddFieldDefinitionToDomainContentTypeSpec extends ObjectBehavior
{
    const TYPE_IDENTIFIER = 'test';
    const TYPE_NAME = 'TestContent';
    const FIELD_IDENTIFIER = 'test_field';
    const FIELD_NAME = 'testField';
    const FIELD_DESCRIPTION = ['eng-GB' => 'Description'];
    const FIELD_TYPE = 'ezstring';

    /**
     * @var ContentType\FieldDefinition
     */
    private $defaultFieldDefinition;

    public function __construct()
    {
        $this->defaultFieldDefinition = $this->buildFieldDefinition(self::FIELD_TYPE);
    }
    function let(NameHelper $nameHelper)
    {
        $nameHelper
            ->domainContentTypeName(
                Argument::type(ContentType\ContentType::class)
            )
            ->willReturn(self::TYPE_NAME);

        $nameHelper
            ->fieldDefinitionField(
                Argument::type(ContentType\FieldDefinition::class)
            )
            ->willReturn(self::FIELD_NAME);

        $this->setNameHelper($nameHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddFieldDefinitionToDomainContentType::class);
    }

    function it_adds_the_field_definition_to_the_domain_content_type(Builder $schema)
    {
        $this->work($schema, $this->buildArguments());

        $schema->addFieldToType(
            self::TYPE_NAME,
            FieldArgument::hasName(self::FIELD_NAME)
        )->shouldHaveBeenCalled();
    }

    function it_uses_the_FieldDefinition_description_as_the_Field_description(Builder $schema)
    {
        $this->work($schema, $this->buildArguments());

        $schema->addFieldToType(
            self::TYPE_NAME,
            FieldArgument::hasDescription(self::FIELD_DESCRIPTION['eng-GB'])
        )->shouldHaveBeenCalled();
    }

    function it_uses_the_FieldDefinition_field_type_identifier_to_determine_the_type(Builder $schema)
    {
        foreach (AddFieldDefinitionToDomainContentType::TYPES_MAP as $eZType => $graphQLType) {
            $schema->addFieldToType(
                self::TYPE_NAME,
                FieldArgument::hasType($graphQLType)
            )->shouldBeCalled();
            $this->work($schema, $this->buildArguments($eZType));
        }

    }

    protected function buildArguments($fieldTypeIdentifier = null): array
    {
        $return = [
            'ContentTypeGroup' => new ContentType\ContentTypeGroup(),
            'ContentType' => new ContentType\ContentType(['identifier' => self::TYPE_IDENTIFIER]),
            'FieldDefinition' => $fieldTypeIdentifier ? $this->buildFieldDefinition($fieldTypeIdentifier) : $this->defaultFieldDefinition
        ];

        return $return;
    }

    private function buildFieldDefinition($fieldTypeIdentifier)
    {
        return new ContentType\FieldDefinition([
            'identifier' => self::FIELD_IDENTIFIER,
            'descriptions' => self::FIELD_DESCRIPTION,
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
        ]);
    }
}
