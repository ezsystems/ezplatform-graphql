<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Menu;

use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\MenuResolver;
use EzSystems\EzPlatformGraphQL\Menu\MenuService;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Iterator;
use Generator;
use GraphQL\Type\Definition\InputType;

class MenuDomainIterator implements Iterator
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\Menu\MenuService
     */
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * @inheritDoc
     */
    public function init(Builder $schema)
    {
        $schema->addType(new Builder\Input\Type('MenuCollection', 'object'));
        $schema->addFieldToType('Domain', new Builder\Input\Field('menus', 'MenuCollection', ['resolve' => []]));
    }

    /**
     * @inheritDoc
     */
    public function iterate(): Generator
    {
        foreach ($this->menuService->loadAllMenus() as $menu) {
            yield ['Menu' => $menu];
        }
    }
}
