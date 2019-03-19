<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use Overblog\GraphQLBundle\Definition\Argument;

class ObjectStateGroupResolver
{
    /** @var \eZ\Publish\API\Repository\ObjectStateService */
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
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function resolveObjectStateGroupById(Argument $args): ObjectStateGroup
    {
        return $this->objectStateService->loadObjectStateGroup($args['id']);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function resolveObjectStateGroups(): array
    {
        return $this->objectStateService->loadObjectStateGroups();
    }
}
