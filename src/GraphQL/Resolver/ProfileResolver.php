<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

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

    public function resolveMyProfile()
    {
        $user = $this->security->getUser();
        return $user;
    }
}
