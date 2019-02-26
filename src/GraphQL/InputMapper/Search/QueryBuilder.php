<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion;

class QueryBuilder
{
    /**
     * @var Criterion[]
     */
    private $criteria = [];

    /**
     * @var Query\SortClause
     */
    protected $sortBy;

    public function addCriterion(Query\Criterion $criterion): void
    {
        $this->criteria[] = $criterion;
    }

    public function setSortBy(Query\SortClause $sortClause): void
    {
        $this->sortBy = $sortClause;
    }

    public function buildQuery(): Query
    {
        $query = new Query();

        if (count($this->criteria) === 0) {
            return $query;
        }

        $query->filter = count($this->criteria) === 1 ? $this->criteria[0] : new Query\Criterion\LogicalAnd($this->criteria);

        if ($this->sortBy) {
            $query->sortClauses = $this->sortBy;
        }

        return $query;
    }
}
