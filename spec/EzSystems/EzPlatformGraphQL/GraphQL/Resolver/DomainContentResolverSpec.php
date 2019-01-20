<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\DomainContentResolver;
use eZ\Publish\API\Repository\Repository;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use PhpSpec\ObjectBehavior;

class DomainContentResolverSpec extends ObjectBehavior
{
    function let(
        Repository $repository,
        TypeResolver $typeResolver,
        SearchQueryMapper $queryMapper,
        ContentLoader $contentLoader,
        ContentTypeLoader $contentTypeLoader
    ) {
        $this->beConstructedWith($repository, $typeResolver, $queryMapper, $contentLoader, $contentTypeLoader);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DomainContentResolver::class);
    }
}
