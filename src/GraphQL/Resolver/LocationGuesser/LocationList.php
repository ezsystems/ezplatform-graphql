<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * List of locations used by the LocationGuesser.
 */
interface LocationList
{
    public function addLocation(Location $location): void;

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \EzSystems\EzPlatformGraphQL\Exception\MultipleValidLocationsException
     * @throws \EzSystems\EzPlatformGraphQL\Exception\NoValidLocationsException
     */
    public function getLocation(): Location;

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function getLocations(): array;

    public function hasOneLocation(): bool;

    public function removeLocation(Location $location): void;
}
