<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class JWTUser implements UserInterface
{
    /** @var \Symfony\Component\Security\Core\User\UserInterface */
    private $wrappedUser;

    /** @var string|null */
    private $userIdentifier;

    public function __construct(UserInterface $wrappedUser, ?string $userIdentifier)
    {
        $this->wrappedUser = $wrappedUser;
        $this->userIdentifier = $userIdentifier;
    }

    public function getPassword(): ?string
    {
        return $this->wrappedUser->getPassword();
    }

    public function eraseCredentials(): void
    {
        $this->wrappedUser->eraseCredentials();
    }

    public function getRoles(): array
    {
        return $this->wrappedUser->getRoles();
    }

    public function getSalt(): ?string
    {
        return $this->wrappedUser->getSalt();
    }

    public function getUsername(): string
    {
        return $this->userIdentifier ?? $this->wrappedUser->getUsername();
    }

    public function getWrappedUser(): UserInterface
    {
        return $this->wrappedUser;
    }
}
