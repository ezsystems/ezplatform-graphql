<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryInputVisitor;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SearchQueryMapperSpec extends ObjectBehavior
{
    function let(QueryInputVisitor $visitor1, QueryInputVisitor $visitor2, QueryBuilder $queryBuilder)
    {
        $this->beConstructedWith(['visitor_1' => $visitor1, 'visitor_2' => $visitor2, 'visitor_3' => $visitor2], $queryBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchQueryMapper::class);
    }

    function it_uses_one_visitor_and_not_the_other(
        QueryInputVisitor $visitor1,
        QueryInputVisitor $visitor2,
        QueryBuilder $queryBuilder
    ) {
        $queryBuilder->buildQuery()->shouldBeCalledOnce();

        $visitor1->visit($queryBuilder, 'a_value')->shouldBeCalled();
        $visitor2->visit()->shouldNotBeCalled();

        $this->mapInputToQuery(['visitor_1' => 'a_value']);
    }

    function it_visits_same_visitor_more_than_once(
        QueryInputVisitor $visitor1,
        QueryInputVisitor $visitor2,
        QueryBuilder $queryBuilder
    ) {
        $queryBuilder->buildQuery()->shouldBeCalledOnce();

        $visitor1->visit($queryBuilder, 'a_value')->shouldBeCalledOnce();
        $visitor2->visit($queryBuilder, Argument::type('string'))->shouldBeCalledTimes(2);

        $this->mapInputToQuery([
            'visitor_1' => 'a_value',
            'visitor_2' => 'value2',
            'visitor_3' => 'value3'
        ]);
    }
}
