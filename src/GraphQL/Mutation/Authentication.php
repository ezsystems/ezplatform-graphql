<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class Authentication
{
    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    /**
     * @var \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface
     */
    private $tokenManager;

    public function __construct(UserService $userService, JWTTokenManagerInterface $tokenManager)
    {
        $this->userService = $userService;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function createToken($args)
    {
        $username = $args['username'];
        $password = $args['password'];

        $apiUser = $this->userService->loadUserByLogin($username);
        if (!$this->userService->checkUserCredentials($apiUser, $password)) {
            return ['message' => 'Wrong username or password', 'token' => null];
        }

        $token = $this->tokenManager->create(new User($apiUser, ['ROLE_USER']));

        return ['token' => $token];
    }
}
