<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Relay;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Output\ConnectionBuilder;

class SearchResolver
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
     * @param $args
     * @return Connection
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function searchContent($args)
    {
        $queryArg = $args['query'];

        $query = new Query();
        $criteria = [];

        if (isset($queryArg['ContentTypeIdentifier'])) {
            $criteria[] = new Query\Criterion\ContentTypeIdentifier($queryArg['ContentTypeIdentifier']);
        }

        if (isset($queryArg['Text'])) {
            foreach ($queryArg['Text'] as $text) {
                $criteria[] = new Query\Criterion\FullText($text);
            }
        }

        if (count($criteria) === 0) {
            return null;
        }
        $query->filter = count($criteria) > 1 ? new Query\Criterion\LogicalAnd($criteria) : $criteria[0];
        $searchResult = $this->searchService->findContentInfo($query);

        $contentItems = array_map(
            function (SearchHit $hit) {
                return $hit->valueObject;
            },
            $searchResult->searchHits
        );

        $connection = ConnectionBuilder::connectionFromArraySlice(
            $contentItems,
            $args,
            [
                'sliceStart' => 0,
                'arrayLength' => $searchResult->totalCount,
            ]
        );
        $connection->sliceSize = count($contentItems);

        return $connection;
    }
}
