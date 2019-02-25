<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use PhpSpec\ObjectBehavior;

class QueryBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(QueryBuilder::class);
    }

    function it_builds_a_query_when_no_citerion_is_defined()
    {
        $query = $this->buildQuery();
        $query->shouldBeAnInstanceOf(Query::class);
    }

    function it_builds_a_query_when_only_one_criterion_is_criterion($criterion)
    {
        $criterion->beADoubleOf(Criterion::class);
        $this->addCriterion($criterion);
        $query = $this->buildQuery();
        $query->filter->shouldBe($criterion);
    }

    function it_builds_a_query_when_more_than_criterion_is_passed($criterion1, $criterion2)
    {
        $criterion1->beADoubleOf(Criterion::class);
        $criterion2->beADoubleOf(Criterion::class);

        $this->addCriterion($criterion1);
        $this->addCriterion($criterion2);

        $query = $this->buildQuery();

        $query->filter->shouldBeAnInstanceOf(Criterion\LogicalAnd::class);
    }

    function it_builds_a_query_with_sort_criterias($sortBy)
    {
        $sortBy->beADoubleOf(Query\SortClause::class);
        $this->setSortBy($sortBy);

        $query = $this->buildQuery();
        $query->sortClauses->shouldBeArray();
    }
}
