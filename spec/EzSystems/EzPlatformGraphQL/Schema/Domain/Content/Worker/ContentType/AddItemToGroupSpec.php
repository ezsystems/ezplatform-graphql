<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType\AddItemToGroup;

class AddItemToGroupSpec extends ContentTypeWorkerBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AddItemToGroup::class);
    }
}
