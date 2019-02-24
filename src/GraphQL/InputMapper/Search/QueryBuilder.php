<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use eZ\Publish\API\Repository\Values\Content\Query;

class QueryBuilder
{
    /**
     * @var array
     */
    private $criterions = [];

    /**
     * @var Query\SortClause
     */
    protected $sortBy;

    public function addCriterion(Query\Criterion $criterion): void
    {
        $this->criterions[] = $criterion;
    }

    public function setSortBy(Query\SortClause $sortClause): void
    {
        $this->sortBy = $sortClause;
    }

    public function buildQuery(): Query
    {
        $query = new Query();

        if (count($this->criterions) === 0) {
            return $query;
        }

        $query->filter = count($this->criterions) === 1 ? $this->criterions[0] : new Query\Criterion\LogicalAnd($this->criterions);

        print 'bh';
        if ($this->sortBy) {
            print 'ah';
            $query->sortClauses = $this->sortBy;
        }

        return $query;
    }
}
