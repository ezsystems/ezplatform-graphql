<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentTypeGroup;

use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentTypeGroup\DefineDomainGroupTypes;
use spec\EzSystems\EzPlatformGraphQL\Tools\TypeArgument;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use Prophecy\Argument;

class DefineDomainGroupTypesSpec extends ContentTypeGroupWorkerBehavior
{
    const GROUP_TYPES_TYPE = 'DomainGroupTestTypes';

    public function let(NameHelper $nameHelper)
    {
        $this->setNameHelper($nameHelper);

        $nameHelper
            ->domainGroupTypesName(Argument::type(ContentTypeGroup::class))
            ->willReturn(self::GROUP_TYPES_TYPE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DefineDomainGroupTypes::class);
    }

    function it_can_not_work_if_args_do_not_have_ContentTypeGroup(
        SchemaBuilder $schema
    )
    {
        $this->canWork($schema, [])->shouldBe(false);
    }

    function it_can_not_work_if_the_type_is_already_defined(
        SchemaBuilder $schema
    )
    {
        $schema->hasType(self::GROUP_TYPES_TYPE)->willReturn(true);
        $this->canWork($schema, $this->args())->shouldBe(false);
    }

    function it_defines_the_DomainGroupTypes_object(
        SchemaBuilder $schema
    )
    {
        $schema
            ->addType(
                Argument::allOf(
                    TypeArgument::isNamed(self::GROUP_TYPES_TYPE),
                    TypeArgument::hasType('object')
                )
            )
            ->shouldBeCalled();
        $this->work($schema, $this->args());
    }
}
