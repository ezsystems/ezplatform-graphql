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
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;
use InvalidArgumentException;

class SearchQueryMapper
{
    /**
     * @var QueryInputVisitor[]
     */
    private $queryInputCriteriaVisitors;

    /**
     * @var QueryInputVisitor[]
     */
    private $queryInputSortClauseVisitors;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    public function __construct(
        array $queryInputCriteriaVisitors,
        array $queryInputSortClauseVisitors,
        QueryBuilder $queryBuilder
    ) {
        $this->queryInputCriteriaVisitors = $queryInputCriteriaVisitors;
        $this->queryInputSortClauseVisitors = $queryInputSortClauseVisitors;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function mapInputToQuery(array $inputArray) : Query
    {
        foreach ($inputArray as $inputField => $inputValue) {
            if (isset($this->queryInputCriteriaVisitors[$inputField])) {
                $this->queryInputCriteriaVisitors[$inputField]->visit($this->queryBuilder, $inputValue);
            }

            if (isset($this->queryInputSortClauseVisitors[$inputField])) {
                $this->queryInputSortClauseVisitors[$inputField]->visit($this->queryBuilder, $inputValue);
            }
        }

        return $this->queryBuilder->buildQuery();
    }
}
