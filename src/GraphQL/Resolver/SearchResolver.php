<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\LocationLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Item;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

/**
 * @internal
 */
final class SearchResolver
{
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var SearchQueryMapper
     */
    private $queryMapper;

    /**
     * @var ContentLoader
     */
    private $contentLoader;

    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\LocationLoader
     */
    private $locationLoader;

    public function __construct(ContentLoader $contentLoader, LocationLoader $locationLoader, SearchService $searchService, SearchQueryMapper $queryMapper)
    {
        $this->contentLoader = $contentLoader;
        $this->locationLoader = $locationLoader;
        $this->searchService = $searchService;
        $this->queryMapper = $queryMapper;
    }

    public function searchContent($args)
    {
        return $this->contentLoader->find(
            $this->queryMapper->mapInputToQuery($args['query'])
        );
    }

    public function searchContentOfTypeAsConnection($contentTypeIdentifier, $args)
    {
        $query = $args['query'] ?: [];
        $query['ContentTypeIdentifier'] = $contentTypeIdentifier;
        $query['sortBy'] = $args['sortBy'];
        $query = $this->queryMapper->mapInputToQuery($query);

        $paginator = new Paginator(function ($offset, $limit) use ($query) {
            $query->offset = $offset;
            $query->limit = $limit ?? 10;

            return $this->contentLoader->find($query);
        });

        return $paginator->auto(
            $args,
            function () use ($query) {
                return $this->contentLoader->count($query);
            }
        );
    }

    public function searchLocationsOfTypeAsConnection($contentTypeIdentifier, $args)
    {
        $query = $args['query'] ?: [];
        $query['ContentTypeIdentifier'] = $contentTypeIdentifier;
        $query['sortBy'] = $args['sortBy'];
        $query = $this->queryMapper->mapInputToLocationQuery($query);

        $paginator = new Paginator(function ($offset, $limit) use ($query) {
            $query->offset = $offset;
            $query->limit = $limit ?? 10;

            return $this->locationLoader->find($query);
        });

        return $paginator->auto(
            $args,
            function () use ($query) {
                return $this->locationLoader->count($query);
            }
        );
    }

    public function searchItemsOfTypeAsConnection(string $contentTypeIdentifier, $args): Connection
    {
        $query = $args['query'] ?: [];
        $query['ContentTypeIdentifier'] = $contentTypeIdentifier;
        $query['sortBy'] = $args['sortBy'];
        $query = $this->queryMapper->mapInputToLocationQuery($query);

        $paginator = new Paginator(function ($offset, $limit) use ($query) {
            $query->offset = $offset;
            $query->limit = $limit ?? 10;

            return array_map(
                function(Location $location) {
                    return new Item($location);
                },
                $this->locationLoader->find($query)
            );
        });

        return $paginator->auto(
            $args,
            function () use ($query) {
                return $this->locationLoader->count($query);
            }
        );
    }
}
