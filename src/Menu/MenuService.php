<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformGraphQL\Menu;


class MenuService
{
    /**
     * @var Menu[]
     */
    private $menus;

    public function __construct()
    {
        $mainMenu = new Menu('main', [
            new MenuItem('Item A', '/itemA', [new MenuItem('Sub item A', '/itemA/subitemA')]),
            new MenuItem('Item B', '/itemA', [new MenuItem('Sub item B', '/itemB/subitemB')]),
        ]);

        $this->menus = ['main' => $mainMenu];
    }

    public function loadMenuByIdentifier(string $identifier): Menu
    {
        return $this->menus[$identifier];
    }

    /**
     * @return Menu[]
     */
    public function loadAllMenus(): array
    {
        return $this->menus;
    }
}
