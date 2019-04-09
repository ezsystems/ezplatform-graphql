<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content;

use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NameHelperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NameHelper::class);
    }

    function it_removes_special_characters_from_ContentTypeGroup_identifier()
    {
        $contentTypeGroup = new ContentTypeGroup(['identifier' => 'Name with-hyphen']);
        $this->domainGroupName($contentTypeGroup)->shouldBe('DomainGroupNameWithHyphen');
    }
}
