<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values;
use eZ\Publish\Core\FieldType;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\DomainContentResolver;
use eZ\Publish\API\Repository\Repository;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use PhpSpec\ObjectBehavior;

class DomainContentResolverSpec extends ObjectBehavior
{
    const CONTENT_ID = 1;

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

    function it_resolves_relation_field_values_with_one_item_to_a_single_content(ContentLoader $contentLoader)
    {
        $field = new Field(['value' => new FieldType\Relation\Value(self::CONTENT_ID)]);
        $content = new Values\Content\Query\Criterion\ContentId(1);
        $contentLoader->findSingle(new Values\Content\Query\Criterion\ContentId(self::CONTENT_ID))->willReturn($content);

        $this
            ->resolveDomainRelationFieldValue($field, false)
            ->shouldReturn($content);
    }
}
