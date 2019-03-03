<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\SortClauses;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\SortClauseVisitor;

class SortClauseClass implements SortClauseVisitor
{
    /**
     * @var string
     */
    private $sortClauseClass;

    public function __construct(string $sortClauseClass)
    {
        $this->sortClauseClass = $sortClauseClass;
    }

    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        if (empty($value)) {
            $value = Query::SORT_ASC;
        }

        $queryBuilder->addSortClause(new $this->sortClauseClass($value));
    }
}
