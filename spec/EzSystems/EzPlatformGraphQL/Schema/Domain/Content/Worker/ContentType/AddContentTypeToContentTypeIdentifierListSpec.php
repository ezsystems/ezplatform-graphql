<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType\AddContentTypeToContentTypeIdentifierList;
use spec\EzSystems\EzPlatformGraphQL\Tools\EnumValueArgument;
use Prophecy\Argument;

class AddContentTypeToContentTypeIdentifierListSpec extends ContentTypeWorkerBehavior
{
    const ENUM_TYPE = 'ContentTypeIdentifier';

    public function let(NameHelper $nameHelper)
    {
        $this->setNameHelper($nameHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddContentTypeToContentTypeIdentifierList::class);
    }

    function it_can_not_work_if_args_do_not_have_a_ContentType(SchemaBuilder $schema)
    {
        $args = $this->args();
        unset($args['ContentType']);
        $this->canWork($schema, [])->shouldBe(false);
    }

    function it_can_not_work_if_the_ContentTypeIdentifier_enum_is_not_defined(SchemaBuilder $schema)
    {
        $schema->hasType(self::ENUM_TYPE)->willReturn(false);
        $this->canWork($schema, $this->args())->shouldBe(false);
    }

    function it_adds_a_field_for_the_ContentType_to_the_ContentTypeIdentifier_enum(SchemaBuilder $schema)
    {
        $schema
            ->addValueToEnum(
                self::ENUM_TYPE,
                Argument::allOf(
                    EnumValueArgument::withName(self::TYPE_IDENTIFIER),
                    EnumValueArgument::withDescription(self::TYPE_DESCRIPTION)
                )
            )
            ->shouldBeCalled();
        $this->work($schema, $this->args());
    }
}
