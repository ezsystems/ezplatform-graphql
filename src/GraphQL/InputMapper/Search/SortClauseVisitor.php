<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

interface SortClauseVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void;
}
