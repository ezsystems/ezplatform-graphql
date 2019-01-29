<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;

interface QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void;
}
