<?php
namespace spec\EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\ContentTypeGroup;

use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use PhpSpec\ObjectBehavior;

abstract class ContentTypeGroupWorkerBehavior extends ObjectBehavior
{
    const GROUP_IDENTIFIER = 'test_group';
    const GROUP_DESCRIPTION = 'Description of the group';

    protected function args(): array
    {
        return [
            'ContentTypeGroup' => new ContentTypeGroup([
                'identifier' => self::GROUP_IDENTIFIER,
                'descriptions' => ['eng-GB' => self::GROUP_DESCRIPTION]
            ])
        ];
    }

}