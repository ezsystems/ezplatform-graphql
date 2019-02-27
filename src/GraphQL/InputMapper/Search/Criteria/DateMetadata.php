<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criteria;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;

abstract class DateMetadata implements QueryInputVisitor
{
    protected function visitCriterion(QueryBuilder $queryBuilder, $value, string $criterion): void
    {
        $dateOperatorsMap = [
            'on' => Query\Criterion\Operator::EQ,
            'before' => Query\Criterion\Operator::LTE,
            'after' => Query\Criterion\Operator::GTE,
        ];

        foreach ($value as $operator => $dateString) {
            if (!isset($dateOperatorsMap[$operator])) {
                echo "Not a valid operator\n";
                continue;
            }

            $queryBuilder->addCriterion(new Query\Criterion\DateMetadata(
                $criterion,
                $dateOperatorsMap[$operator],
                strtotime($dateString)
            ));
        }
    }
}
