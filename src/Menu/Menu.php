<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Menu;

class Menu
{
    /**
     * @var MenuItem[]
     */
    public $items;

    /**
     * @var string
     */
    public $identifier;

    public function __construct(string $identifier, array $items)
    {
        $this->items = $items;
        $this->identifier = $identifier;
    }
}
