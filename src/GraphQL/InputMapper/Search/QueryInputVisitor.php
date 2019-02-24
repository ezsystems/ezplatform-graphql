<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

interface QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void;
}
