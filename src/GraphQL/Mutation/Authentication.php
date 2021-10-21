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
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class Authentication
{
    /** @var \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface */
    private $tokenManager;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \eZ\Publish\Core\MVC\Symfony\Security\Authentication\AuthenticatorInterface|null */
    private $authenticator;

    public function __construct(
        JWTTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        ?AuthenticatorInterface $authenticator = null
    ) {
        $this->tokenManager = $tokenManager;
        $this->requestStack = $requestStack;
        $this->authenticator = $authenticator;
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
            $user = $this->getAuthenticator()->authenticate($request)->getUser();

            $token = $this->tokenManager->create(
                new JWTUser($user, $username)
            );

            return ['token' => $token];
        } catch (AuthenticationException $e) {
            return ['message' => 'Wrong username or password', 'token' => null];
        }
    }

    private function getAuthenticator(): AuthenticatorInterface
    {
        if (null === $this->authenticator) {
            throw new \RuntimeException(
                sprintf(
                    "No %s instance injected. Ensure 'ezpublish_rest_session' is configured under your firewall",
                    AuthenticatorInterface::class
                )
            );
        }

        return $this->authenticator;
    }
}
