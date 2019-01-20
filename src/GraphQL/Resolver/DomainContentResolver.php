<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\SearchQueryMapper;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\FieldType;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

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
     * @param \Overblog\GraphQLBundle\Definition\Argument $args
     * @param $contentTypeIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function resolveDomainContentItem(Argument $args, $contentTypeIdentifier)
    {
        if (isset($args['id'])) {
            $criterion = new Query\Criterion\ContentId($args['id']);
        } elseif (isset($args['remoteId'])) {
            $criterion = new Query\Criterion\RemoteId($args['remoteId']);
        } elseif (isset($args['locationId'])) {
            $criterion = new Query\Criterion\LocationId($args['locationId']);
        } else {
            throw new UserError("Missing required argument id, remoteId or locationId");
        }

        $content = $this->contentLoader->findSingle($criterion);

        // @todo consider optimizing using a map of contentTypeId
        $contentType = $this->contentTypeLoader->load($content->contentInfo->contentTypeId);

        if ($contentType->identifier !== $contentTypeIdentifier) {
            throw new UserError("Content {$content->contentInfo->id} is not of type '$contentTypeIdentifier'");
        }

        return $content;
    }

    /**
     * @param string $contentTypeIdentifier
     *
     * @param \Overblog\GraphQLBundle\Definition\Argument $args
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
        if (!$field->value instanceof FieldType\RelationList\Value) {
            throw new UserError("$field->fieldTypeIdentifier is not a RelationList field value");
        }

        if ($multiple) {
            if (count($field->value->destinationContentIds) > 0) {
                return $this->contentLoader->find(new Query(
                    ['filter' => new Query\Criterion\ContentId($field->value->destinationContentIds)]
                ));
            } else {
                return [];
            }
        } else {
            return
                isset($fieldValue->destinationContentIds[0])
                    ? $this->contentLoader->findSingle(new Query\Criterion\ContentId($field->value->destinationContentIds[0]))
                    : null;
        }
    }

    public function ResolveDomainContentType(Content $content)
    {
        return $this->makeDomainContentTypeName(
            $this->contentTypeLoader->loadByIdentifier($content->contentInfo->contentTypeId)
        );
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
}
