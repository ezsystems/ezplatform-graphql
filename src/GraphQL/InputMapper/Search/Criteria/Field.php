<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criteria;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;

class Field implements QueryInputVisitor
{
    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $criteria = [];

        if (isset($value['target'])) {
            $criteria[] = $this->mapInputToFieldCriterion($value);
        } else {
            $criteria = array_merge(
                $criteria,
                array_map(
                    function($input) {
                        return $this->mapInputToFieldCriterion($input);
                    },
                    $value
                )
            );
        }

        foreach ($criteria as $criterion) {
            $queryBuilder->addCriterion($criterion);
        }
    }

    private function mapInputToFieldCriterion($input)
    {
        $operators = ['in', 'eq', 'like', 'contains', 'between', 'lt', 'lte', 'gt', 'gte'];
        foreach ($operators as $opString) {
            if (isset($input[$opString])) {
                $value = $input[$opString];
                $operator = constant('eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator::' . strtoupper($opString));
            }
        }

        if (!isset($operator)) {
            throw new InvalidArgumentException("Unspecified operator");
        }

        return new Query\Criterion\Field($input['target'], $operator, $value);
    }
}
