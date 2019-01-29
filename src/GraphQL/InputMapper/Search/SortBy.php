<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;

class SortBy implements QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $queryBuilder->sortBy = array_map(
            function ($sortClauseClass) {

                static $lastSortClause;

                if ($sortClauseClass === Query::SORT_DESC) {
                    if (!$lastSortClause instanceof Query\SortClause) {
                        return null;
                    }

                    $lastSortClause->direction = $sortClauseClass;
                    return null;
                }

                if (!class_exists($sortClauseClass)) {
                    return null;
                }

                if (!in_array(Query\SortClause::class, class_parents($sortClauseClass))) {
                    return null;
                }

                return $lastSortClause = new $sortClauseClass;
            },
            $value
        );
    }
}
