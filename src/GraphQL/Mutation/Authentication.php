<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation;

use eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface;
use Ibexa\Rest\Server\Security\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class Authentication
{
    /**
     * @var \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface|null
     */
    private $authenticator;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(
        JWTTokenManagerInterface $tokenManager,
        AuthenticatorInterface $authenticator,
        RequestStack $requestStack
    ) {
        $this->tokenManager = $tokenManager;
        $this->authenticator = $authenticator;
        $this->requestStack = $requestStack;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function createToken($args): array
    {
        $username = $args['username'];
        $password = $args['password'];

        $request = $this->requestStack->getCurrentRequest();
        $request->attributes->set('username', $username);
        $request->attributes->set('password', (string) $password);

        try {
            $user = $this->authenticator->authenticate($request)->getUser();

            $token = $this->tokenManager->create(
                new JWTUser($user, $username)
            );

            return ['token' => $token];
        } catch (AuthenticationException $e) {
            throw new UnauthorizedException('Invalid login or password', $request->getPathInfo());
        }
    }
}
