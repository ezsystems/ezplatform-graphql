<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Builder\Input;

abstract class Input
{
    public function __construct(array $properties)
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                throw new \InvalidArgumentException("No property named $name");
            }
            $this->$name = $value;
        }
    }
}
