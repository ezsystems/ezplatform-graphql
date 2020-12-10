<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Filters a Location based on the tree root site settings.
 * Only locations that are within the site root or one of the excluded paths are kept.
 */
class TreeRootLocationFilter implements LocationFilter
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    private $urlAliasService;

    public function __construct(LocationService $locationService, UrlAliasService $urlAliasService, ConfigResolverInterface $configResolver)
    {
        $this->locationService = $locationService;
        $this->configResolver = $configResolver;
        $this->urlAliasService = $urlAliasService;
    }

    public function filter(Content $content, LocationList $locationList): void
    {
        foreach ($locationList->getLocations() as $location) {
            if (!$this->locationIsInTreeRoot($location) && !$this->locationPrefixIsExcluded($location)) {
                $locationList->removeLocation($location);
            }
        }
    }

    /**
     * Checks if a location is valid in regards to the tree root setting.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function locationIsInTreeRoot(Location $location): bool
    {
        static $rootLocations = [];

        $treeRootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        if (!isset($rootLocations[$treeRootLocationId])) {
            $rootLocations[$treeRootLocationId] = $this->locationService->loadLocation($treeRootLocationId);
        }

        return $this->containsRootPath($location->path, $rootLocations[$treeRootLocationId]->path);
    }

    /**
     * Tests if the location is excluded from tree root.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $candidateLocation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function locationPrefixIsExcluded(Location $candidateLocation): bool
    {
        static $excludedLocations = null;

        if ($excludedLocations === null) {
            $excludedUriPrefixes = $this->configResolver->getParameter('content.tree_root.excluded_uri_prefixes');
            if (empty($excludedUriPrefixes)) {
                return false;
            }
            foreach ($excludedUriPrefixes as $uri) {
                $urlAlias = $this->urlAliasService->lookup($uri);
                if ($urlAlias->type === URLAlias::LOCATION) {
                    $excludedLocations[] = $this->locationService->loadLocation($urlAlias->destination);
                }
            }
        }

        foreach ($excludedLocations as $excludedLocation) {
            if ($this->containsRootPath($candidateLocation->path, $excludedLocation->path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $path
     * @param array $rootPath
     */
    private function containsRootPath(array $path, array $rootPath): bool
    {
        return array_slice($path, 0, count($rootPath)) === $rootPath;
    }
}
