<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query;

class DateMetadata implements SearchCriterion
{
    public function map($value): array
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

            $criteria[] = new Query\Criterion\DateMetadata(
                static::TARGET,
                $dateOperatorsMap[$operator],
                strtotime($dateString)
            );
        }

        return $criteria;
    }

}
