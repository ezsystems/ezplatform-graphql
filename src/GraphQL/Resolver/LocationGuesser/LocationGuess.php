<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * The result of the guesser's work.
 */
class LocationGuess
{
    /**
     * @var Location
     */
    public $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }
}
