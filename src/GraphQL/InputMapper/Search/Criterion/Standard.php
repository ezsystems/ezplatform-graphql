<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;

class Standard implements QueryInputVisitor
{
    private $criterionClass;

    public function __construct($criterionClass)
    {
        $this->criterionClass = $criterionClass;
    }

    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $queryBuilder->addCriterion(new {$this->criterionClass}($value));
    }
}
