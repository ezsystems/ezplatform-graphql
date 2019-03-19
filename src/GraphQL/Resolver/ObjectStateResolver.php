<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use Overblog\GraphQLBundle\Definition\Argument;

class ObjectStateResolver
{
    /**
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    private $objectStateService;

    /**
     * @param \eZ\Publish\API\Repository\ObjectStateService $objectStateService
     */
    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $args
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function resolveObjectStateById(Argument $args): ObjectState
    {
        return $this->objectStateService->loadObjectState($args['id']);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function resolveObjectStatesByGroup(ObjectStateGroup $objectStateGroup): array
    {
        return $this->objectStateService->loadObjectStates($objectStateGroup);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $args
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function resolveObjectStatesByGroupId(Argument $args): array
    {
        $group = $this->objectStateService->loadObjectStateGroup($args['groupId']);

        return $this->objectStateService->loadObjectStates($group);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function resolveObjectStateByContentInfo(ContentInfo $contentInfo): array
    {
        $objectStates = [];
        foreach ($this->objectStateService->loadObjectStateGroups() as $group) {
            $objectStates[] = $this->objectStateService->getContentState($contentInfo, $group);
        }

        return $objectStates;
    }
}
