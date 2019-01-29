<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;

class DateMetadata implements QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $dateOperatorsMap = [
            'on' => Query\Criterion\Operator::EQ,
            'before' => Query\Criterion\Operator::LTE,
            'after' => Query\Criterion\Operator::GTE,
        ];

        $criteria = [];
        foreach ($value as $operator => $dateString) {
            if (!isset($dateOperatorsMap[$operator])) {
                echo "Not a valid operator\n";
                continue;
            }

            $queryBuilder->addCriterion(new Query\Criterion\DateMetadata(
                static::TARGET,
                $dateOperatorsMap[$operator],
                strtotime($dateString)
            ));
        }
    }
}
