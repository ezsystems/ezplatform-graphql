<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\ContentCollectionFilterBuilder;

/**
 * Returns the locations from the current site (e.g. within its tree root).
 */
class CurrentSiteLocationProvider implements LocationProvider
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;
    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\ContentCollectionFilterBuilder
     */
    private $filterBuilder;

    public function __construct(SearchService $searchService, ContentCollectionFilterBuilder $filterBuilder)
    {
        $this->searchService = $searchService;
        $this->filterBuilder = $filterBuilder;
    }

    public function getLocations(Content $content): LocationList
    {
        $query = new LocationQuery([
            'filter' => new Criterion\LogicalAnd([
                $this->filterBuilder->buildFilter(),
                new Criterion\ContentId($content->id),
            ]),
        ]);

        $list = new ObjectStorageLocationList($content);
        foreach ($this->searchService->findLocations($query)->searchHits as $searchHit) {
            $list->addLocation($searchHit->valueObject);
        }

        return $list;
    }
}
