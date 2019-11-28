<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;

/**
 * @internal
 */
class ObjectStateGroupResolver
{
    /** @var \eZ\Publish\API\Repository\ObjectStateService */
    private $objectStateService;

    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
    }

    public function resolveObjectStateGroupById(Argument $args): ObjectStateGroup
    {
        try {
            return $this->objectStateService->loadObjectStateGroup($args['id']);
        } catch (NotFoundException $e) {
            throw new UserError("Object State Group with ID: {$args['id']} was not found.");
        }
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function resolveObjectStateGroups(): array
    {
        return $this->objectStateService->loadObjectStateGroups();
    }
}
