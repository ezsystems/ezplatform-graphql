<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\LocationLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\QueryMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\ItemFactory;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Item;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @internal
 */
final class ItemResolver
{
    /** @var \Overblog\GraphQLBundle\Resolver\TypeResolver */
    private $typeResolver;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\QueryMapper */
    private $queryMapper;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader */
    private $contentLoader;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader */
    private $contentTypeLoader;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\LocationLoader */
    private $locationLoader;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser */
    private $locationGuesser;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\ItemFactory */
    private $itemFactory;

    public function __construct(
        TypeResolver $typeResolver,
        QueryMapper $queryMapper,
        ContentLoader $contentLoader,
        ContentTypeLoader $contentTypeLoader,
        LocationLoader $locationLoader,
        LocationGuesser $locationGuesser,
        ItemFactory $itemFactory
    ) {
        $this->typeResolver = $typeResolver;
        $this->queryMapper = $queryMapper;
        $this->contentLoader = $contentLoader;
        $this->contentTypeLoader = $contentTypeLoader;
        $this->locationLoader = $locationLoader;
        $this->locationGuesser = $locationGuesser;
        $this->itemFactory = $itemFactory;
    }

    /**
     * Resolves a domain content item by id, and checks that it is of the requested type.
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument|array $args
     * @param string|null $contentTypeIdentifier
     *
     * @throws \GraphQL\Error\UserError if $contentTypeIdentifier was specified, and the loaded item's type didn't match it
     * @throws \GraphQL\Error\UserError if no argument was provided
     */
    public function resolveItemOfType($args, $contentTypeIdentifier): Item
    {
        if (isset($args['id'])) {
            $item = $this->itemFactory->fromContent(
                $this->contentLoader->findSingle(new Query\Criterion\ContentId($args['id']))
            );
        } elseif (isset($args['contentId'])) {
            $item = $this->itemFactory->fromContent(
                $content = $this->contentLoader->findSingle(new Query\Criterion\ContentId($args['contentId']))
            );
        } elseif (isset($args['remoteId'])) {
            $item = $this->itemFactory->fromContent(
                $this->contentLoader->findSingle(new Query\Criterion\RemoteId($args['remoteId']))
            );
        } elseif (isset($args['locationId'])) {
            $item = $this->itemFactory->fromLocation(
                $this->locationLoader->findById($args['locationId'])
            );
        } elseif (isset($args['locationRemoteId'])) {
            $item = $this->itemFactory->fromLocation(
                $this->locationLoader->findByRemoteId($args['locationRemoteId'])
            );
        } elseif (isset($args['urlAlias'])) {
            $item = $this->itemFactory->fromLocation(
                $this->locationLoader->findByUrlAlias($args['urlAlias'])
            );
        } else {
            throw new UserError('Missing required argument contentId, remoteId, locationId or locationRemoteId');
        }

        $contentType = $item->getContentInfo()->getContentType();

        if ($contentType->identifier !== $contentTypeIdentifier) {
            throw new UserError("Content {$item->getContentInfo()->id} is not of type '$contentTypeIdentifier'");
        }

        return $item;
    }

    public function resolveItemFieldValue(Item $item, $fieldDefinitionIdentifier): Field
    {
        return Field::fromField($item->getContent()->getField($fieldDefinitionIdentifier));
    }

    public function resolveItemsOfTypeAsConnection(string $contentTypeIdentifier, $args): Connection
    {
        $query = $args['query'] ?: [];
        $query['ContentTypeIdentifier'] = $contentTypeIdentifier;
        $query['sortBy'] = $args['sortBy'];
        $query = $this->queryMapper->mapInputToLocationQuery($query);

        $paginator = new Paginator(function ($offset, $limit) use ($query) {
            $query->offset = $offset;
            $query->limit = $limit ?? 10;

            return array_map(
                function (Content $content) {
                    return $this->itemFactory->fromContent($content);
                },
                $this->contentLoader->find($query)
            );
        });

        return $paginator->auto(
            $args,
            function () use ($query) {
                return $this->contentLoader->count($query);
            }
        );
    }

    public function resolveItemType(Item $item): string
    {
        $typeName = $this->makeDomainContentTypeName(
            $item->getContentInfo()->getContentType()
        );

        return  ($this->typeResolver->hasSolution($typeName))
            ? $typeName
            : 'UntypedItem';
    }

    private function makeDomainContentTypeName(ContentType $contentType): string
    {
        $converter = new CamelCaseToSnakeCaseNameConverter(null, false);

        return $converter->denormalize($contentType->identifier) . 'Item';
    }
}
