<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * @internal
 */
class ContentResolver
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentService $contentService, SearchService $searchService, ContentTypeService $contentTypeService)
    {
        $this->contentService = $contentService;
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
    }

    public function findContentByType($contentTypeId)
    {
        $searchResults = $this->searchService->findContentInfo(
            new Query([
                'filter' => new Query\Criterion\ContentTypeId($contentTypeId),
            ])
        );

        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResults->searchHits
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function findContentRelations(ContentInfo $contentInfo, $version = null)
    {
        return $this->contentService->loadRelations(
            $this->contentService->loadVersionInfo($contentInfo, $version)
        );
    }

    public function findContentReverseRelations(ContentInfo $contentInfo)
    {
        return $this->contentService->loadReverseRelations($contentInfo);
    }

    public function resolveContent($args)
    {
        if (isset($args['id'])) {
            return $this->contentService->loadContentInfo($args['id']);
        }

        if (isset($args['remoteId'])) {
            return $this->contentService->loadContentInfoByRemoteId($args['remoteId']);
        }
    }

    public function resolveContentById($contentId)
    {
        return $this->contentService->loadContentInfo($contentId);
    }

    public function resolveContentByIdList(array $contentIdList)
    {
        try {
            $searchResults = $this->searchService->findContentInfo(
                new Query([
                    'filter' => new Query\Criterion\ContentId($contentIdList),
                ])
            );
        } catch (\Exception $e) {
            return [];
        }

        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResults->searchHits
        );
    }

    public function resolveContentVersions($contentId)
    {
        return $this->contentService->loadVersions(
            $this->contentService->loadContentInfo($contentId)
        );
    }

    public function resolveCurrentVersion(ContentInfo $contentInfo): VersionInfo
    {
        return $this->contentService->loadVersionInfo($contentInfo);
    }
}
