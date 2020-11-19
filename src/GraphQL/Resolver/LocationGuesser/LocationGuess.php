<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformGraphQL\Exception;

class LocationGuess
{
    private $content;

    private $locations;

    public function __construct(Content $content, array $locations)
    {
        $this->content = $content;
        $this->locations = $locations;
    }

    /**
     * Returns the location guess result if the guess was successful.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \EzSystems\EzPlatformGraphQL\Exception\MultipleValidLocationsException
     * @throws \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\NoValidLocationsException
     */
    public function getLocation(): Location
    {
        if (count($this->locations) > 1) {
            throw new Exception\MultipleValidLocationsException($this->content, $this->locations);
        } else if (count($this->locations) === 0) {
            throw new NoValidLocationsException($this->content);
        }

        return $this->locations[0];
    }

    public function isSuccessful(): bool
    {
        return count($this->locations) === 1;
    }
}
