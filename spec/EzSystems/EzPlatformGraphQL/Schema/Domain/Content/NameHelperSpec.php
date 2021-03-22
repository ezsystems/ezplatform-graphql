<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content;

use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NameHelperSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['id'=>'id_']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NameHelper::class);
    }

    function it_removes_special_characters_from_ContentTypeGroup_identifier()
    {
        $contentTypeGroup = new ContentTypeGroup(['identifier' => 'Name with-hyphen']);
        $this->domainGroupName($contentTypeGroup)->shouldBe('DomainGroupNameWithHyphen');
    }

    function it_removes_field_type_identifier_colisions()
    {
        $fieldDefinition = new FieldDefinition(['identifier' => 'id']);
        $this->fieldDefinitionField($fieldDefinition)->shouldBe('id_');
    }
}
