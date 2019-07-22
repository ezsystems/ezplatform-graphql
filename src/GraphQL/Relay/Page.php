<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Relay;

/**
 * A Page in a PageAwareConnection.
 */
class Page
{
    /** @var int */
    public $number;

    /** @var string */
    public $cursor;

    public function __construct(int $number, string $cursor)
    {
        $this->number = $number;
        $this->cursor = $cursor;
    }
}
