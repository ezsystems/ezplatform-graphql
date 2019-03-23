<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository as API;
use eZ\Publish\API\Repository\Values as RepositoryValues;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class DomainContentMutationResolver
{
    /**
     * @var API\Repository
     */
    private $repository;

    /**
     * @var \EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper
     */
    private $nameHelper;

    public function __construct(API\Repository $repository, NameHelper $nameHelper)
    {
        $this->repository = $repository;
        $this->nameHelper = $nameHelper;
    }

    public function deleteDomainContent(Argument $args)
    {
        $globalId = null;

        if (isset($args['id'])) {
            $globalId = $args['id'];
            $idArray = GlobalId::fromGlobalId($args['id']);
            $contentId = $idArray['id'];
        } elseif (isset($args['contentId'])) {
            $contentId = $args['contentId'];
        } else {
            throw new UserError('One argument out of id or contentId is required');
        }

        try {
            $contentInfo = $this->getContentService()->loadContentInfo($contentId);
        } catch (API\Exceptions\NotFoundException $e) {
            throw new UserError("No content item was found with id $contentId");
        } catch (API\Exceptions\UnauthorizedException $e) {
            throw new UserError("You are not authorized to load the content item with id $contentId");
        }
        if (!isset($globalId)) {
            $globalId = GlobalId::toGlobalId(
                $this->resolveDomainContentType($contentInfo),
                $contentId
            );
        }

        // @todo check type of domain object

        try {
            $this->getContentService()->deleteContent($contentInfo);
        } catch (API\Exceptions\UnauthorizedException $e) {
            throw new UserError("You are not authorized to delete the content item with id $contentInfo->id");
        }

        return [
            'id' => $globalId,
            'contentId' => $contentId,
        ];
    }

    public function resolveDomainContentType(RepositoryValues\Content\ContentInfo $contentInfo)
    {
        static $contentTypesMap = [], $contentTypesLoadErrors = [];

        if (!isset($contentTypesMap[$contentInfo->contentTypeId])) {
            try {
                $contentTypesMap[$contentInfo->contentTypeId] = $this->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
            } catch (\Exception $e) {
                $contentTypesLoadErrors[$contentInfo->contentTypeId] = $e;
                throw $e;
            }
        }

        return $this->makeDomainContentTypeName($contentTypesMap[$contentInfo->contentTypeId]);
    }

    private function makeDomainContentTypeName(RepositoryValues\ContentType\ContentType $contentType)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter(null, false);

        return $converter->denormalize($contentType->identifier) . 'Content';
    }

    /**
     * @return API\ContentService
     */
    private function getContentService()
    {
        return $this->repository->getContentService();
    }

    /**
     * @return API\ContentTypeService
     */
    private function getContentTypeService()
    {
        return $this->repository->getContentTypeService();
    }
}
