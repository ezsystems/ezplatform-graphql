<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use eZ\Publish\API\Repository\Values\Content\Query;

class QueryBuilder
{
    /**
     * @var array
     */
    private $criterions = [];

    /** @var Query\SortClause */
    protected $sortBy;

    public function addCriterion(Query\Criterion $criterion): void
    {
        $this->criterions[] = $criterion;
    }

    public function buildQuery(): Query
    {

        $query = new Query();

        if (count($this->criterions) === 0) {
            return $query;
        }

        $query->filter = count($this->criterions) === 1 ? $this->criterions[0] : new Query\Criterion\LogicalAnd($this->criterions);

        if ($this->sortBy) {
            $query->sortClauses = $this->sortBy;
        }

        return $query;
    }
}
