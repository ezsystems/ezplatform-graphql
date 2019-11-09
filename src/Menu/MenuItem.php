<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Menu;

class MenuItem
{
    public $label;

    public $uri;

    /**
     * @var MenuItem[]
     */
    public $children = [];

    public function __construct($label, $uri, ?array $children = [])
    {
        $this->label = $label;
        $this->uri = $uri;
        $this->children = $children;
    }

    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }
}
