<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use DateTime;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\REST\Server\Input\Parser\Criterion\Operator;
use PhpSpec\ObjectBehavior;

class SearchQueryMapperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SearchQueryMapper::class);
    }

    public function it_maps_ContentTypeIdentifier_to_a_ContentTypeIdentifier_criterion()
    {
        $this->mapInputToQuery(['ContentTypeIdentifier' => ['article']])->shouldFilterByContentType(['article']);
    }

    public function it_maps_Text_to_a_FullText_criterion()
    {
        $this
            ->mapInputToQuery(['Text' => 'graphql'])
            ->shouldFilterByFullText('graphql');
    }

    public function it_maps_Modified_before_to_a_created_lte_DateMetaData_criterion()
    {
        $this
            ->mapInputToQuery(['Modified' => ['before' => '1977/05/04']])
            ->shouldFilterByDateModified(Query\Criterion\Operator::LTE, '1977/05/04');
    }

    public function it_maps_Modified_on_to_a_created_eq_DateMetaData_criterion()
    {
        $this
            ->mapInputToQuery(['Modified' => ['on' => '1977/05/04']])
            ->shouldFilterByDateModified(Query\Criterion\Operator::EQ, '1977/05/04');
    }

    public function it_maps_Modified_after_to_a_created_gte_DateMetaData_criterion()
    {
        $this
            ->mapInputToQuery(['Modified' => ['after' => '1977/05/04']])
            ->shouldFilterByDateModified(Query\Criterion\Operator::GTE, '1977/05/04');
    }

    public function it_maps_Created_before_to_a_created_lte_DateMetaData_criterion()
    {
        $this
            ->mapInputToQuery(['Created' => ['before' => '1977/05/04']])
            ->shouldFilterByDateCreated(Query\Criterion\Operator::LTE, '1977/05/04');
    }

    public function it_maps_Created_on_to_a_created_eq_DateMetaData_criterion()
    {
        $this
            ->mapInputToQuery(['Created' => ['on' => '1977/05/04']])
            ->shouldFilterByDateCreated(Query\Criterion\Operator::EQ, '1977/05/04');
    }

    public function it_maps_Created_after_to_a_created_gte_DateMetaData_criterion()
    {
        $this
            ->mapInputToQuery(['Created' => ['after' => '1977/05/04']])
            ->shouldFilterByDateCreated(Query\Criterion\Operator::GTE, '1977/05/04');
    }

    function it_maps_Field_to_a_Field_criterion()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'eq' => 'bar']])
            ->shouldFilterByField('target_field');
    }

    function it_maps_Field_target_to_the_Field_criterion_target()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'eq' => 'bar']])
            ->shouldFilterByField('target_field', Query\Criterion\Operator::EQ, 'bar');
    }

    function it_maps_Field_with_value_at_operator_key_to_the_Field_criterion_value()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'eq' => 'bar']])
            ->shouldFilterByField('target_field', null, 'bar');
    }

    function it_maps_Field_operator_eq_to_Field_criterion_operator_EQ()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'eq' => 'bar']])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::EQ);
    }

    function it_maps_Field_operator_in_to_Field_criterion_operator_IN()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'eq' => 'bar']])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::EQ);
    }

    function it_maps_Field_operator_lt_to_Field_criterion_operator_LT()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'lt' => 'bar']])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::LT);
    }

    function it_maps_Field_operator_lte_to_Field_criterion_operator_LTE()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'lte' => 'bar']])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::LTE);
    }

    function it_maps_Field_operator_gte_to_Field_criterion_operator_GTE()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'gte' => 'bar']])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::GTE);
    }

    function it_maps_Field_operator_gt_to_Field_criterion_operator_GT()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'gt' => 'bar']])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::GT);
    }

    function it_maps_Field_operator_between_to_Field_criterion_operator_BETWEEN()
    {
        $this
            ->mapInputToQuery(['Field' => ['target' => 'target_field', 'between' => [10, 20]]])
            ->shouldFilterByFieldWithOperator(Query\Criterion\Operator::BETWEEN);
    }

    public function getMatchers(): array
    {
        return [
            'filterByContentType' => function(Query $query, array $contentTypes) {
                $criterion = $this->findCriterionInQueryFilter(
                    $query,
                    Query\Criterion\ContentTypeIdentifier::class
                );

                if ($criterion === null) {
                    return false;
                }

                return $criterion->value === $contentTypes;
            },
            'filterByFullText' => function(Query $query, $text) {
                $criterion = $this->findCriterionInQueryFilter(
                    $query,
                    Query\Criterion\FullText::class
                );

                if ($criterion === null) {
                    return false;
                }

                return $criterion->value === $text;
            },
            'filterByDateModified' => function(Query $query, $operator, $date) {
                $criterion = $this->findCriterionInQueryFilter($query, Query\Criterion\DateMetadata::class);

                if ($criterion === null) {
                    return false;
                }

                if ($criterion->target !== Query\Criterion\DateMetadata::MODIFIED) {
                    return false;
                }

                return $criterion->operator == $operator
                    && $criterion->value[0] == strtotime($date);
            },
            'filterByDateCreated' => function(Query $query, $operator, $date) {
                $criterion = $this->findCriterionInQueryFilter($query, Query\Criterion\DateMetadata::class);

                if ($criterion === null) {
                    return false;
                }

                if ($criterion->target !== Query\Criterion\DateMetadata::CREATED) {
                    return false;
                }

                return $criterion->operator == $operator
                    && $criterion->value[0] == strtotime($date);
            },
            'filterByField' => function(Query $query, $field, $operator = null, $value = null) {
                $criterion = $this->findCriterionInQueryFilter($query, Query\Criterion\Field::class);

                if ($criterion === null) {
                    return false;
                }

                if ($criterion->target !== $field) {
                    return false;
                }

                if ($operator !== null && $criterion->operator != $operator) {
                    return false;
                }
                return ($value === null || $criterion->value == $value);
            },
            'filterByFieldWithOperator' => function(Query $query, $operator) {
                $criterion = $this->findCriterionInQueryFilter($query, Query\Criterion\Field::class);
                if ($criterion === null) {
                    return false;
                }

                return $criterion->operator == $operator;
            }
        ];
    }

    private function findCriterionInQueryFilter(Query $query, $searchedCriterionClass)
    {
        if ($query->filter instanceof Query\Criterion\LogicalOperator) {
            return $this->findCriterionInCriterion($query->filter, $searchedCriterionClass);
        } else {
            if ($query->filter instanceof $searchedCriterionClass) {
                return $query->filter;
            }
        }

        return null;
    }

    private function findCriterionInCriterion(Query\Criterion\LogicalOperator $logicalCriterion, $searchedCriterionClass)
    {
        foreach ($logicalCriterion->criteria as $criterion) {
            if ($criterion instanceof Query\Criterion\LogicalOperator) {
                $criterion = $this->findCriterionInCriterion($criterion, $searchedCriterionClass);
                if ($criterion !== null) {
                    return $criterion;
                }
            }

            if ($criterion instanceof $searchedCriterionClass) {
                return $criterion;
            }
        }

        return null;
    }
}
