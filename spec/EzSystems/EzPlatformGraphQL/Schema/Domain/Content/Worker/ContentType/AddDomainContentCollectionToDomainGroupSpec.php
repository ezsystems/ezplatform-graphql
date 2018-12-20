<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType\AddDomainContentCollectionToDomainGroup;
use spec\EzSystems\EzPlatformGraphQL\Tools\ContentTypeArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\ContentTypeGroupArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\TypeArgument;
use Prophecy\Argument;

class AddDomainContentCollectionToDomainGroupSpec extends ContentTypeWorkerBehavior
{
    const GROUP_TYPE = 'DomainGroupTestGroup';
    const TYPE_TYPE = 'TestTypeContent';
    const COLLECTION_FIELD = 'testTypes';

    function let(NameHelper $nameHelper)
    {
        $this->setNameHelper($nameHelper);

        $nameHelper
            ->domainGroupName(ContentTypeGroupArgument::withIdentifier(self::GROUP_IDENTIFIER))
            ->willReturn(self::GROUP_TYPE);

        $nameHelper
            ->domainContentCollectionField(ContentTypeArgument::withIdentifier(self::TYPE_IDENTIFIER))
            ->willReturn(self::COLLECTION_FIELD);

        $nameHelper
            ->domainContentName(ContentTypeArgument::withIdentifier(self::TYPE_IDENTIFIER))
            ->willReturn(self::TYPE_TYPE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddDomainContentCollectionToDomainGroup::class);
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
        $schema->hasTypeWithField(self::GROUP_TYPE, self::COLLECTION_FIELD)->willReturn(true);
        $this->canWork($schema, $this->args())->shouldBe(false);
    }

    function it_adds_a_collection_field_for_the_ContentType_to_the_ContentTypeGroup(SchemaBuilder $schema)
    {
        $schema
            ->addFieldToType(
                self::GROUP_TYPE,
                Argument::allOf(
                    FieldArgument::hasName(self::COLLECTION_FIELD),
                    FieldArgument::hasType('[' . self::TYPE_TYPE . ']'),
                    FieldArgument::hasDescription(self::TYPE_DESCRIPTION),
                    FieldArgument::withResolver('DomainContentItemsByTypeIdentifier')
                )
            )
            ->shouldBeCalled();

        $schema
            ->addArgToField(
                self::GROUP_TYPE,
                self::COLLECTION_FIELD,
                Argument::allOf(
                    FieldArgArgument::withName('query'),
                    FieldArgArgument::withType('ContentSearchQuery')
                )
            )
            ->shouldBeCalled();

        $schema
            ->addArgToField(
                self::GROUP_TYPE,
                self::COLLECTION_FIELD,
                Argument::allOf(
                    FieldArgArgument::withName('sortBy'),
                    FieldArgArgument::withType('[SortByOptions]')
                )
            )
            ->shouldBeCalled();

        $this->work($schema, $this->args());
    }
}
