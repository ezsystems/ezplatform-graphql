<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

class SortBy implements QueryInputVisitor
{
    /**
     * @var SortClauseVisitor[]
     */
    private $sortClauseVisitors;

    public function __construct(array $sortClauseVisitors = [])
    {
        $this->sortClauseVisitors = $sortClauseVisitors;
    }

    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        foreach ($value as $inputKey => $inputValue) {
            if (isset($this->sortClauseVisitors[$inputKey])) {
                $this->sortClauseVisitors[$inputKey]->visit($queryBuilder, $inputValue);
            }
        }
    }
}
