<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\DateMetadata;

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
}
