<?php

namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentType\AddItemToDomainGroup;

class AddDomainContentToDomainGroupSpec extends ContentTypeWorkerBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AddItemToDomainGroup::class);
    }
}
