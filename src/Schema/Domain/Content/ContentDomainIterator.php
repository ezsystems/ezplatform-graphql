<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content;

use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Iterator;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use eZ\Publish\API\Repository\ContentTypeService;
use Generator;

class ContentDomainIterator implements Iterator
{
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function init(Builder $schema)
    {
        $schema->addType(
            new Input\Type('Domain', 'object', ['inherits' => ['Platform']])
        );
    }

    public function iterate(): Generator
    {
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            yield ['ContentTypeGroup' => $contentTypeGroup];

            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                yield ['ContentTypeGroup' => $contentTypeGroup]
                    + ['ContentType' => $contentType];

                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    yield ['ContentTypeGroup' => $contentTypeGroup]
                        + ['ContentType' => $contentType]
                        + ['FieldDefinition' => $fieldDefinition];
                }
            }
        }
    }
}
