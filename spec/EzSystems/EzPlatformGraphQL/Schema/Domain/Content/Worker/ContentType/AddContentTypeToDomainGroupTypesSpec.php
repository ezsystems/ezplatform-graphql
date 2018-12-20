<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Builder\SchemaBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType\AddContentTypeToDomainGroupTypes;
use spec\EzSystems\EzPlatformGraphQL\Tools\ContentTypeArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\ContentTypeGroupArgument;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgument;
use Prophecy\Argument;

class AddContentTypeToDomainGroupTypesSpec extends ContentTypeWorkerBehavior
{
    const GROUP_TYPES_TYPE = 'DomainGroupTestGroupTypes';

    const TYPE_FIELD = 'testType';
    const TYPE_TYPE = 'TestTypeContentType';

    public function let(NameHelper $nameHelper)
    {
        $this->setNameHelper($nameHelper);

        $nameHelper
            ->domainContentField(
                ContentTypeArgument::withIdentifier(self::TYPE_IDENTIFIER)
            )
            ->willReturn(self::TYPE_FIELD);

        $nameHelper
            ->domainContentTypeName(
                ContentTypeArgument::withIdentifier(self::TYPE_IDENTIFIER)
            )
            ->willReturn(self::TYPE_TYPE);

        $nameHelper
            ->domainGroupTypesName(
                ContentTypeGroupArgument::withIdentifier(self::GROUP_IDENTIFIER)
            )
            ->willReturn(self::GROUP_TYPES_TYPE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddContentTypeToDomainGroupTypes::class);
    }

    function it_can_not_work_if_args_do_not_have_a_ContentTypeGroup(SchemaBuilder $schema)
    {
        $this->canWork($schema, [])->shouldBe(false);
    }

    function it_can_not_work_if_args_do_not_have_a_ContentType(SchemaBuilder $schema)
    {
        $args = $this->args();
        unset($args['ContentType']);
        $this->canWork($schema, $args)->shouldBe(false);
    }

    function it_can_not_work_if_the_field_is_already_defined(SchemaBuilder $schema)
    {
        $schema->hasTypeWithField(self::GROUP_TYPES_TYPE, self::TYPE_FIELD)->willReturn(true);
        $this->canWork($schema, $this->args())->shouldBe(false);
    }

    function it_adds_a_field_for_the_ContentType_to_the_DomainGroupTypes_object(SchemaBuilder $schema)
    {
        $schema
            ->addFieldToType(
                self::GROUP_TYPES_TYPE,
                Argument::allOf(
                    FieldArgument::hasName(self::TYPE_FIELD),
                    FieldArgument::hasType(self::TYPE_TYPE)
                )
            )
            ->shouldBeCalled();
        $this->work($schema, $this->args());
    }
}
