<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\SortClauses;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;

class SortClauseClass implements QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $queryBuilder->addSortClause(array_map(
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
        ));
    }

}
