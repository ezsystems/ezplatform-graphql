<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query;

class Field implements SearchCriterion
{
    public function map($value): array
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

        return $criteria;
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
