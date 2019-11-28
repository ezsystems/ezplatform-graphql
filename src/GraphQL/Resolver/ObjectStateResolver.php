<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;

/**
 * @internal
 */
class ObjectStateResolver
{
    /** @var \eZ\Publish\API\Repository\ObjectStateService */
    private $objectStateService;

    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
    }

    public function resolveObjectStateById(Argument $args): ObjectState
    {
        try {
            return $this->objectStateService->loadObjectState($args['id']);
        } catch (NotFoundException $e) {
            throw new UserError("Object State with ID: {$args['id']} was not found.");
        }
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function resolveObjectStatesByGroup(ObjectStateGroup $objectStateGroup): array
    {
        return $this->objectStateService->loadObjectStates($objectStateGroup);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function resolveObjectStatesByGroupId(Argument $args): array
    {
        try {
            $group = $this->objectStateService->loadObjectStateGroup($args['groupId']);
        } catch (NotFoundException $e) {
            throw new UserError("Object State Group with ID: {$args['groupId']} was not found.");
        }

        return $this->objectStateService->loadObjectStates($group);
    }

    /**
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
