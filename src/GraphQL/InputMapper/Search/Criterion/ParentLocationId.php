<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;

class ParentLocationId implements QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $queryBuilder->addCriterion(new Query\Criterion\ParentLocationId($value));
    }
}
