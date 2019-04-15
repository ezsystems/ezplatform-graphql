<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values;
use Overblog\GraphQLBundle\Error\UserError;

/**
 * @internal
 */
class UserResolver
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var PermissionResolver
     */
    private $permissionResolver;

    public function __construct(UserService $userService, LocationService $locationService, PermissionResolver $permissionResolver)
    {
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->permissionResolver = $permissionResolver;
    }

    public function resolveUser($args)
    {
        if (isset($args['id'])) {
            return $this->userService->loadUser($args['id']);
        }

        if (isset($args['email'])) {
            return $this->userService->loadUsersByEmail($args['email']);
        }

        if (isset($args['login'])) {
            return $this->userService->loadUserByLogin($args['login']);
        }
    }

    public function resolveUserById($userId)
    {
        return $this->userService->loadUser($userId);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    public function resolveUserGroupsByUserId($userId)
    {
        return $this->userService->loadUserGroupsOfUser(
            $this->userService->loadUser($userId)
        );
    }

    public function resolveUsersOfGroup(Values\User\UserGroup $userGroup)
    {
        return $this->userService->loadUsersOfUserGroup(
            $userGroup
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    public function resolveUserGroupById($userGroupId)
    {
        return $this->userService->loadUserGroup($userGroupId);
    }

    public function resolveUserGroupSubGroups(Values\User\UserGroup $userGroup)
    {
        return $this->userService->loadSubUserGroups($userGroup);
    }

    public function resolveUserGroups($args)
    {
        return $this->userService->loadSubUserGroups(
            $this->userService->loadUserGroup(
                $this->locationService->loadLocation($args['id'])->contentId
            )
        );
    }

    public function resolveContentFields(Values\Content\Content $content, $args)
    {
        if (isset($args['identifier'])) {
            return [$content->getField($args['identifier'])];
        }
        return $content->getFieldsByLanguage();
    }

    public function resolveCurrentUser(): Values\User\User
    {
        try {
            return $this->userService->loadUser(
                $this->permissionResolver->getCurrentUserReference()->getUserId()
            );
        } catch (NotFoundException $e) {
            throw new UserError("User not found");
        }
    }
}
