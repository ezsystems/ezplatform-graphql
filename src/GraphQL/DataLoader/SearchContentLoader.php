<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\DataLoader;

use eZ\Publish\API\Repository\Exceptions as ApiException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\Exception\ArgumentsException;

/**
 * @internal
 */
class SearchContentLoader implements ContentLoader
{
    /**
     * @var SearchService
     */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Loads a list of content items given a Query Criterion.
     *
     * @param Query $query A Query Criterion. To use multiple criteria, group them with a LogicalAnd.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function find(Query $query): array
    {
        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $this->searchService->findContent($query)->searchHits
        );
    }

    /**
     * Loads a single content item given a Query Criterion.
     *
     * @param Criterion $filter A Query Criterion. Use Criterion\ContentId, Criterion\RemoteId or Criterion\LocationId for basic loading.
     *
     * @throws ArgumentsException
     */
    public function findSingle(Criterion $filter): Content
    {
        try {
            return $this->searchService->findSingle($filter);
        } catch (ApiException\InvalidArgumentException $e) {
        } catch (ApiException\NotFoundException $e) {
            throw new ArgumentsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Counts the results of a query.
     *
     * @return int
     *
     * @throws ArgumentsException
     */
    public function count(Query $query)
    {
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $countQuery->offset = 0;

        try {
            return $this->searchService->findContent($countQuery)->totalCount;
        } catch (ApiException\InvalidArgumentException $e) {
            throw new ArgumentsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
