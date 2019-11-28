<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\FieldType;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @internal
 */
class DomainContentResolver
{
    /**
     * @var \Overblog\GraphQLBundle\Resolver\TypeResolver
     */
    private $typeResolver;

    /**
     * @var SearchQueryMapper
     */
    private $queryMapper;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ContentLoader
     */
    private $contentLoader;

    /**
     * @var ContentTypeLoader
     */
    private $contentTypeLoader;

    public function __construct(
        Repository $repository,
        TypeResolver $typeResolver,
        SearchQueryMapper $queryMapper,
        ContentLoader $contentLoader,
        ContentTypeLoader $contentTypeLoader)
    {
        $this->repository = $repository;
        $this->typeResolver = $typeResolver;
        $this->queryMapper = $queryMapper;
        $this->contentLoader = $contentLoader;
        $this->contentTypeLoader = $contentTypeLoader;
    }

    public function resolveDomainContentItems($contentTypeIdentifier, $query = null)
    {
        return $this->findContentItemsByTypeIdentifier($contentTypeIdentifier, $query);
    }

    /**
     * Resolves a domain content item by id, and checks that it is of the requested type.
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument|array $args
     * @param string|null $contentTypeIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \GraphQL\Error\UserError if $contentTypeIdentifier was specified, and the loaded item's type didn't match it
     * @throws \GraphQL\Error\UserError if no argument was provided
     */
    public function resolveDomainContentItem($args, $contentTypeIdentifier)
    {
        if (isset($args['id'])) {
            $criterion = new Query\Criterion\ContentId($args['id']);
        } elseif (isset($args['remoteId'])) {
            $criterion = new Query\Criterion\RemoteId($args['remoteId']);
        } elseif (isset($args['locationId'])) {
            $criterion = new Query\Criterion\LocationId($args['locationId']);
        } else {
            throw new UserError('Missing required argument id, remoteId or locationId');
        }

        $content = $this->contentLoader->findSingle($criterion);

        $contentType = $this->contentTypeLoader->load($content->contentInfo->contentTypeId);

        if (null !== $contentTypeIdentifier && $contentType->identifier !== $contentTypeIdentifier) {
            throw new UserError("Content {$content->contentInfo->id} is not of type '$contentTypeIdentifier'");
        }

        return $content;
    }

    /**
     * @param string $contentTypeIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    private function findContentItemsByTypeIdentifier($contentTypeIdentifier, Argument $args): array
    {
        $input = $args['query'];
        $input['ContentTypeIdentifier'] = $contentTypeIdentifier;
        if (isset($args['sortBy'])) {
            $input['sortBy'] = $args['sortBy'];
        }

        return $this->contentLoader->find(
            $this->queryMapper->mapInputToQuery($input)
        );
    }

    public function resolveMainUrlAlias(Content $content)
    {
        $aliases = $this->repository->getURLAliasService()->listLocationAliases(
            $this->getLocationService()->loadLocation($content->contentInfo->mainLocationId),
            false
        );

        return isset($aliases[0]->path) ? $aliases[0]->path : null;
    }

    public function resolveDomainFieldValue(Content $content, $fieldDefinitionIdentifier)
    {
        return Field::fromField($content->getField($fieldDefinitionIdentifier));
    }

    public function resolveDomainRelationFieldValue(Field $field, $multiple = false)
    {
        $destinationContentIds = $this->getContentIds($field);

        if (empty($destinationContentIds) || array_key_exists(0, $destinationContentIds) && null === $destinationContentIds[0]) {
            return $multiple ? [] : null;
        }

        $contentItems = $this->contentLoader->find(new Query(
            ['filter' => new Query\Criterion\ContentId($destinationContentIds)]
        ));

        if ($multiple) {
            return array_map(
                function ($contentId) use ($contentItems) {
                    return $contentItems[array_search($contentId, array_column($contentItems, 'id'))];
                },
                $destinationContentIds
            );
        }

        return $contentItems[0] ?? null;
    }

    public function resolveDomainContentType(Content $content)
    {
        $typeName = $this->makeDomainContentTypeName(
            $this->contentTypeLoader->load($content->contentInfo->contentTypeId)
        );

        return  ($this->typeResolver->hasSolution($typeName))
            ? $typeName
            : 'UntypedContent';
    }

    private function makeDomainContentTypeName(ContentType $contentType)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter(null, false);

        return $converter->denormalize($contentType->identifier) . 'Content';
    }

    /**
     * @return \eZ\Publish\API\Repository\LocationService
     */
    private function getLocationService()
    {
        return $this->repository->getLocationService();
    }

    /**
     * @return array
     *
     * @throws UserError if the field isn't a Relation or RelationList value
     */
    private function getContentIds(Field $field)
    {
        if ($field->value instanceof FieldType\RelationList\Value) {
            return $field->value->destinationContentIds;
        } elseif ($field->value instanceof FieldType\Relation\Value) {
            return [$field->value->destinationContentId];
        } else {
            throw new UserError('\$field does not contain a RelationList or Relation Field value');
        }
    }
}
