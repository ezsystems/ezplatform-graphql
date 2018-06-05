<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use BD\EzPlatformGraphQLBundle\GraphQL\Value\ContentFieldValue;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

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

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * FieldType <=> GraphQL type mapping.
     * @var array
     */
    private $typesMap = [
        'ezauthor' => 'AuthorFieldValue',
        'ezgmaplocation' => 'MapLocationFieldValue',
        'ezimage' => 'ImageFieldValue',
        'ezrichtext' => 'RichTextFieldValue',
        'ezstring' => 'TextLineFieldValue',
        'ezobjectrelationlist' => 'RelationListFieldValue',
    ];

    public function __construct(ContentService $contentService, SearchService $searchService, ContentTypeService $contentTypeService, TypeResolver $typeResolver)
    {
        $this->contentService = $contentService;
        $this->searchService = $searchService;
        $this->contentTypeService = $contentTypeService;
        $this->typeResolver = $typeResolver;
    }

    public function findContentByType($contentTypeId)
    {
        $searchResults = $this->searchService->findContentInfo(
            new Query([
                'filter' => new Query\Criterion\ContentTypeId($contentTypeId)
            ])
        );

        return array_map(
            function(SearchHit $searchHit) {
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

    public function findContentReverseRelations(ContentInfo $contentInfo, $version = null)
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
        $searchResults = $this->searchService->findContentInfo(
            new Query([
                'filter' => new Query\Criterion\ContentId($contentIdList)
            ])
        );

        return array_map(
            function(SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResults->searchHits
        );
    }

    public function resolveContentFields($contentId, $args)
    {
        $content = $this->contentService->loadContent(
            $contentId,
            isset($args['languages']) ? $args['languages'] : null,
            isset($args['version']) ? $args['version'] : null,
            isset($args['useAlwaysAvailable']) ? $args['useAlwaysAvailable'] : true
        );

        if (isset($args['identifier']) && count($args['identifier']) === 1) {
            $fields = [$content->getField($args['identifier'][0])];
        } else {
            $fields = $content->getFieldsByLanguage();

            $filteredFields = [];
            if (isset($args['identifier'])) {
                foreach ($fields as $field) {
                    if (in_array($field->fieldDefIdentifier, $args['identifier'])) {
                        $filteredFields[] = $field;
                    }
                }
                $fields = $filteredFields;
            }
        }

        return array_map(
            // This wraps the value as a ContentFieldValue, so that the field definition can be identified later on.
            function(Field $field) use ($content) {
                return new Field(
                    [
                        'id' => $field->id,
                        'value' => new ContentFieldValue(
                            [
                                'contentTypeId' => $content->contentInfo->contentTypeId,
                                'fieldDefIdentifier' => $field->fieldDefIdentifier,
                                'value' => $field->value,
                            ]
                        ),
                        'fieldDefIdentifier' => $field->fieldDefIdentifier,
                        'languageCode' => $field->languageCode,
                    ]
                );
            },
            $fields
        );
    }

    public function resolveContentFieldsInVersion($contentId, $versionNo, $args)
    {
        return $this->resolveContentFields(
            $contentId,
            ['version' => $versionNo] + $args->getRawArguments()
        );
    }

    public function resolveContentVersions($contentId)
    {
        return $this->contentService->loadVersions(
            $this->contentService->loadContentInfo($contentId)
        );
    }

    public function resolveFieldValueType(ContentFieldValue $field)
    {
        static $mapCache = [];

        if (!isset($mapCache[$field->contentTypeId])) {
            $contentType = $this->contentTypeService->loadContentType($field->contentTypeId);
            foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                $mapCache[$contentType->id][$fieldDefinition->identifier] = $fieldDefinition->fieldTypeIdentifier;
            }
        }

        $fieldTypeIdentifier = $mapCache[$field->contentTypeId][$field->fieldDefIdentifier];
        $typeString = isset($this->typesMap[$fieldTypeIdentifier]) ? $this->typesMap[$fieldTypeIdentifier] : 'GenericFieldValue';

        return $this->typeResolver->resolve($typeString);
    }
}
