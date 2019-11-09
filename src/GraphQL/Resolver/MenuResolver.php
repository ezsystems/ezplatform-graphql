<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;


use EzSystems\EzPlatformGraphQL\Menu\Menu;
use EzSystems\EzPlatformGraphQL\Menu\MenuItem;
use EzSystems\EzPlatformGraphQL\Menu\MenuService;

class MenuResolver
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Menu\MenuService
     */
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function resolveMenuByIdentifier(string $identifier): Menu
    {
        return $this->menuService->loadMenuByIdentifier($identifier);
    }
}
