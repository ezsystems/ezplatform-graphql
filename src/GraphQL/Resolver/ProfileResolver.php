<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\Security\Core\Security;

class ProfileResolver
{
    /**
     * @var \Symfony\Component\Security\Core\Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function resolveViewerProfile(): array
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $apiUser = $user->getAPIUser();
            return [
                'username' => $apiUser->login,
                'email' => $apiUser->email,
                'name' => $apiUser->getName(),
                'picture' => $apiUser->getThumbnail(),
            ];
        } else {
            return [];
        }
    }
}
