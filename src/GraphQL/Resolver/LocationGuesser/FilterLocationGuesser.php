<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformGraphQL\Exception\MultipleValidLocationsException;

/**
 * Guesses a location based on voters.
 */
class FilterLocationGuesser implements LocationGuesser
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationFilter[]
     */
    private $filters;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @param LocationFilter[] $filters
     */
    public function __construct(LocationService $locationService, array $filters)
    {
        $this->filters = $filters;
        $this->locationService = $locationService;
    }

    /**
     * @inheritDoc
     */
    public function guessLocation(Content $content): LocationGuess
    {
        $locationList = new LocationList($content);
        foreach ($this->locationService->loadLocations($content->contentInfo) as $location) {
            $locationList->addLocation($location);
        }

        foreach ($this->filters as $filter) {
            $filter->filter($content, $locationList);
            if ($locationList->hasOneLocation()) {
                return new LocationGuess($content, $locationList->getLocations());
            }
        }

        return new LocationGuess($content, $locationList->getLocations());
    }
}
