<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType\AddItemOfTypeConnectionToGroup;
use spec\EzSystems\EzPlatformGraphQL\Tools\ContentTypeArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\ContentTypeGroupArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\TypeArgument;
use Prophecy\Argument;

class AddItemOfTypeConnectionToGroupSpec extends ContentTypeWorkerBehavior
{
    const GROUP_TYPE = 'ItemTestGroup';
    const TYPE_TYPE = 'TestItemConnection';
    const CONNECTION_FIELD = 'testTypes';

    function let(NameHelper $nameHelper)
    {
        $this->setNameHelper($nameHelper);

        $nameHelper
            ->itemGroupName(ContentTypeGroupArgument::withIdentifier(self::GROUP_IDENTIFIER))
            ->willReturn(self::GROUP_TYPE);

        $nameHelper
            ->itemConnectionField(ContentTypeArgument::withIdentifier(self::TYPE_IDENTIFIER))
            ->willReturn(self::CONNECTION_FIELD);

        $nameHelper
            ->itemConnectionName(ContentTypeArgument::withIdentifier(self::TYPE_IDENTIFIER))
            ->willReturn(self::TYPE_TYPE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddItemOfTypeConnectionToGroup::class);
    }

    function it_can_not_work_if_args_do_not_include_a_ContentTypeGroup(SchemaBuilder $schema)
    {
        $this->canWork($schema, [])->shouldBe(false);
    }

    function it_can_not_work_if_args_do_not_include_a_ContentType(SchemaBuilder $schema)
    {
        $args = $this->args();
        unset($args['ContentType']);
        $this->canWork($schema, $args)->shouldBe(false);
    }

    function it_can_not_work_if_the_collection_field_is_already_set(SchemaBuilder $schema)
    {
        $schema->hasTypeWithField(self::GROUP_TYPE, self::CONNECTION_FIELD)->willReturn(true);
        $this->canWork($schema, $this->args())->shouldBe(false);
    }

    function it_adds_a_collection_field_for_the_ContentType_to_the_ContentTypeGroup(SchemaBuilder $schema)
    {
        $schema
            ->addFieldToType(
                self::GROUP_TYPE,
                Argument::allOf(
                    FieldArgument::hasName(self::CONNECTION_FIELD),
                    FieldArgument::hasType(self::TYPE_TYPE),
                    FieldArgument::hasDescription(self::TYPE_DESCRIPTION),
                    FieldArgument::withResolver('ItemsOfTypeAsConnection')
                )
            )
            ->shouldBeCalled();

        $schema
            ->addArgToField(
                self::GROUP_TYPE,
                self::CONNECTION_FIELD,
                Argument::allOf(
                    FieldArgArgument::withName('query'),
                    FieldArgArgument::withType('ContentSearchQuery')
                )
            )
            ->shouldBeCalled();

        $schema
            ->addArgToField(
                self::GROUP_TYPE,
                self::CONNECTION_FIELD,
                Argument::allOf(
                    FieldArgArgument::withName('sortBy'),
                    FieldArgArgument::withType('[SortByOptions]')
                )
            )
            ->shouldBeCalled();

        $this->work($schema, $this->args());
    }
}
