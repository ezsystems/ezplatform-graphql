<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content;

use eZ\Publish\API\Repository\ContentTypeService;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Builder\Input;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Iterator;
use Generator;
use Ibexa\GraphQL\Schema\Domain\NameValidator;

class ContentDomainIterator implements Iterator
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\GraphQL\Schema\Domain\NameValidator */
    private $nameValidator;

    public function __construct(
        ContentTypeService $contentTypeService,
        NameValidator $nameValidator
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->nameValidator = $nameValidator;
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
                if (!$this->nameValidator->isValidName($contentType->identifier)) {
                    $this->nameValidator->generateInvalidNameWarning('Content Type', $contentType->identifier);

                    continue;
                }

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
