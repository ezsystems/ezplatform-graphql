<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

class ContentTypeResolver
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    public function __construct(TypeResolver $typeResolver, ContentTypeService $contentTypeService)
    {
        $this->typeResolver = $typeResolver;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType[]
     */public function resolveContentTypesFromGroup($args)
    {
        if (isset($args['groupId'])) {
            $group = $this->contentTypeService->loadContentTypeGroup($args['groupId']);
        }

        if (isset($args['groupIdentifier'])) {
            $group = $this->contentTypeService->loadContentTypeGroupByIdentifier($args['groupIdentifier']);
        }

        if (isset($group)) {
            $contentTypes = $this->contentTypeService->loadContentTypes($group);
        } else {
            $contentTypes = [];
            foreach ($this->contentTypeService->loadContentTypeGroups() as $group) {
                $contentTypes = array_merge(
                    $contentTypes,
                    $this->contentTypeService->loadContentTypes($group)
                );
            }
        }

        return $contentTypes;
    }

    public function resolveContentTypeById($contentTypeId)
    {
        return $this->contentTypeService->loadContentType($contentTypeId);
    }

    public function resolveContentType($args)
    {
        if (isset($args['id'])) {
            return $this->resolveContentTypeById($args['id']);
        }

        if (isset($args['identifier'])) {
            return $this->contentTypeService->loadContentTypeByIdentifier($args['identifier']);
        }
    }
}
