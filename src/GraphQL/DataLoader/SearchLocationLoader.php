<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\DataLoader;

use eZ\Publish\API\Repository\Exceptions as ApiException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\Exception\ArgumentsException;

/**
 * @internal
 */
class SearchLocationLoader implements LocationLoader
{
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    private $urlAliasService;
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    private $urlAliasGenerator;

    public function __construct(SearchService $searchService, LocationService $locationService, URLAliasService $urlAliasService, ConfigResolverInterface $configResolver, UrlAliasGenerator $urlAliasGenerator)
    {
        $this->searchService = $searchService;
        $this->locationService = $locationService;
        $this->urlAliasService = $urlAliasService;
        $this->configResolver = $configResolver;
        $this->urlAliasGenerator = $urlAliasGenerator;
    }

    public function find(LocationQuery $query): array
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $this->searchService->findLocations($query)->searchHits
        );
    }

    public function findById($id): Location
    {
        try {
            return $this->locationService->loadLocation($id);
        } catch (ApiException\InvalidArgumentException $e) {
        } catch (ApiException\NotFoundException $e) {
            throw new ArgumentsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function findByRemoteId($id): Location
    {
        try {
            return $this->locationService->loadLocationByRemoteId($id);
        } catch (ApiException\InvalidArgumentException $e) {
        } catch (ApiException\NotFoundException $e) {
            throw new ArgumentsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function findByUrlAlias(string $urlAlias): Location
    {
        $alias = $this->getUrlAlias($urlAlias);

        return ($alias->type == URLAlias::LOCATION)
            ? $this->locationService->loadLocation($alias->destination)
            : null;
    }

    /**
     * Counts the results of a query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return int
     *
     * @throws ArgumentsException
     */
    public function count(LocationQuery $query)
    {
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $countQuery->offset = 0;

        try {
            return $this->searchService->findLocations($countQuery)->totalCount;
        } catch (ApiException\InvalidArgumentException $e) {
            throw new ArgumentsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getUrlAlias($pathinfo): URLAlias
    {
        $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        $pathPrefix = $this->urlAliasGenerator->getPathPrefixByRootLocationId($rootLocationId);

        if (
            $rootLocationId !== null &&
            !$this->urlAliasGenerator->isUriPrefixExcluded($pathinfo) &&
            $pathPrefix !== '/'
        ) {
            $urlAlias = $pathPrefix . $pathinfo;
        }

        return $this->urlAliasService->lookup($pathPrefix . $pathinfo);
    }}
