<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code..
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Security\EventSubscriber;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzPlatformUser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent as BaseInteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Subscribes to the security.interactive_login event to set current user reference if user is an instance of an eZ user.
 */
final class AuthenticationEventSubscriber implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(
        PermissionResolver $permissionResolver
    ) {
        $this->permissionResolver = $permissionResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_AUTHENTICATED => [
                ['setPermissionResolverUserReference', 10]
            ]
        ];
    }

    public function setPermissionResolverUserReference(JWTAuthenticatedEvent $event): void
    {
        $user = $event->getToken()->getUser();
        if ($user instanceof EzPlatformUser) {
            $this->permissionResolver->setCurrentUserReference($user->getAPIUser());
        }
    }
}
