<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * Returns all the locations the current user has access to.
 */
class AllAllowedLocationProvider implements LocationProvider
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function getLocations(Content $content): LocationList
    {
        $list = new LocationList($content);

        foreach ($this->locationService->loadLocations($content->contentInfo) as $location) {
            $list->addLocation($location);
        }

        return $list;
    }
}
