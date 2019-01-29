<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 21/09/2018
 * Time: 16:50
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use InvalidArgumentException;

class SearchQueryMapper
{
    private $queryInputVisitors;

    private $queryBuilder;

    public function __construct(array $queryInputVisitors, QueryBuilder $queryBuilder)
    {
        $this->queryInputVisitors = $queryInputVisitors;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
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
