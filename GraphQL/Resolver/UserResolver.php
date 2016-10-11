<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\User\UserGroup;

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

    public function __construct(UserService $userService, LocationService $locationService)
    {
        $this->userService = $userService;
        $this->locationService = $locationService;
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

    public function resolveUsersOfGroup(UserGroup $userGroup)
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

    public function resolveUserGroupSubGroups(UserGroup $userGroup)
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

    public function resolveContentFields(Content $content, $args)
    {
        if (isset($args['identifier'])) {
            return [$content->getField($args['identifier'])];
        }
        return $content->getFieldsByLanguage();
    }
}
