<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;
use InvalidArgumentException;

class SearchQueryMapper
{
    /**
     * @var QueryInputVisitor[]
     */
    private $queryInputVisitors;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(
        array $queryInputVisitors,
        QueryBuilder $queryBuilder
    ) {
        $this->queryInputVisitors = $queryInputVisitors;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param array $inputArray
     * @return Query
     */
    public function mapInputToQuery(array $inputArray) : Query
    {
        foreach ($inputArray as $inputField => $inputValue) {
            if (isset($this->queryInputVisitors[$inputField])) {
                $this->queryInputVisitors[$inputField]->visit($this->queryBuilder, $inputValue);
            }
        }

        return $this->queryBuilder->buildQuery();
    }
}
