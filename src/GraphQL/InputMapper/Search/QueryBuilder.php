<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use eZ\Publish\API\Repository\Values\Content\Query;

class QueryBuilder
{
    /**
     * @var Query\Criterion[]
     */
    private $criteria = [];

    /**
     * @var Query\SortClause[]
     */
    protected $sortClauses = [];

    public function addCriterion(Query\Criterion $criterion): void
    {
        $this->criteria[] = $criterion;
    }

    public function addSortClause(Query\SortClause $sortClause): void
    {
        $this->sortClauses[] = $sortClause;
    }

    public function buildQuery(): Query
    {
        $query = new Query();

        if (count($this->criteria) > 0) {
            $query->filter = count($this->criteria) === 1 ? $this->criteria[0] : new Query\Criterion\LogicalAnd($this->criteria);
        }

        if (count($this->sortClauses) > 0) {
            $query->sortClauses = $this->sortClauses;
        }

        return $query;
    }
}
