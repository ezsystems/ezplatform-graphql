<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class SearchResolver
{
    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var SearchQueryMapper
     */
    private $queryMapper;

    public function __construct(SearchService $searchService, SearchQueryMapper $queryMapper)
    {
        $this->searchService = $searchService;
        $this->queryMapper = $queryMapper;
    }

    public function searchContent($args)
    {
        $query = $this->queryMapper->mapInputToQuery($args['query']);
        $searchResult = $this->searchService->findContentInfo($query);

        return array_map(
            function (SearchHit $hit) {
                return $hit->valueObject;
            },
            $searchResult->searchHits
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
            $searchResults = $this->searchService->findContentInfo($query);

            return array_map(
                function (SearchHit $searchHit) {
                    return $searchHit->valueObject;
                },
                $searchResults->searchHits
            );
        });

        return $paginator->auto(
            $args,
            function() use ($query) {
                $countQuery = clone $query;
                $countQuery->limit = 0;
                $countQuery->offset = 0;

                return $this->searchService->findContentInfo($countQuery)->totalCount;
            }
        );
    }
}
