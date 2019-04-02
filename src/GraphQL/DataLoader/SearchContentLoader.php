<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\DataLoader;

use eZ\Publish\API\Repository\ContentService;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\Exception\NotFoundException;
use eZ\Publish\API\Repository\Exceptions as ApiException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * @internal
 */
class SearchContentLoader implements ContentLoader
{
    /**
     * @var SearchService
     */
    private $searchService;
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    public function __construct(SearchService $searchService, ContentService $contentService)
    {
        $this->searchService = $searchService;
        $this->contentService = $contentService;
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
        if ($results = $this->runIdSearch($query)) {
            return (array)$results;
        }

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
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws NotFoundException
     */
    public function findSingle(Criterion $filter): Content
    {
        try {
            if ($results = $this->runIdSearch($filter)) {
                return $results;
            } else {
                return $this->searchService->findSingle($filter);
            }
        } catch (ApiException\InvalidArgumentException $e) {
        } catch (ApiException\NotFoundException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Counts the results of a query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return int
     *
     * @throws NotFoundException
     */
    public function count(Query $query)
    {
        $countQuery = clone $query;
        $countQuery->limit = 0;
        $countQuery->offset = 0;

        try {
            return $this->searchService->findContent($countQuery)->totalCount;
        } catch (ApiException\InvalidArgumentException $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * If $filter only contains an ID criterion, use the content service for loading.
     *
     * @param $filter
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content|\eZ\Publish\API\Repository\Values\Content\Content[]
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function runIdSearch($filter)
    {
        if ($filter instanceof Criterion\ContentId) {
            $idArgument = $filter->value;
            if (is_array($idArgument) && count($idArgument) > 1) {
                return $this->contentService->loadContentListByContentInfo(
                    $this->contentService->loadContentInfoList($idArgument)
                );
            } else {
                return $this->contentService->loadContent($this->getOneId($idArgument));
            }
        }
    }

    private function getOneId($value)
    {
        if (is_array($value)) {
            if (count($value) === 1) {
                return $value[0];
            }
            throw new \InvalidArgumentException("the id argument is an array with more than one ids");
        } else if (is_numeric($value)) {
            return [$this->contentService->loadContent($value)];
        } else {
            throw new \InvalidArgumentException("the id argument is an array with more than one ids");
        }
    }
}