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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ContentDomainIterator implements Iterator, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\GraphQL\Schema\Domain\NameValidator */
    private $nameValidator;

    public function __construct(ContentTypeService $contentTypeService, NameValidator $nameValidator)
    {
        $this->contentTypeService = $contentTypeService;
        $this->nameValidator = $nameValidator;
        $this->logger = new NullLogger();
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
            if (!$this->nameValidator->isValidName($contentTypeGroup->identifier)) {
                $this->generateInvalidGraphQLNameWarning('Content Type Group', $contentTypeGroup->identifier);
                continue;
            }

            yield ['ContentTypeGroup' => $contentTypeGroup];

            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                if (!$this->nameValidator->isValidName($contentType->identifier)) {
                    $this->generateInvalidGraphQLNameWarning('Content Type', $contentType->identifier);
                    continue;
                }

                yield ['ContentTypeGroup' => $contentTypeGroup]
                    + ['ContentType' => $contentType];

                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    if (!$this->nameValidator->isValidName($fieldDefinition->identifier)) {
                        $this->generateInvalidGraphQLNameWarning('Field Definition', $fieldDefinition->identifier);
                        continue;
                    }

                    yield ['ContentTypeGroup' => $contentTypeGroup]
                        + ['ContentType' => $contentType]
                        + ['FieldDefinition' => $fieldDefinition];
                }
            }
        }
    }

    private function generateInvalidGraphQLNameWarning(string $type, string $name): void
    {
        $message = "Skipped schema generation for %s with identifier '%s'. "
            . 'Please rename given %s according to GraphQL specification '
            . '(http://spec.graphql.org/June2018/#sec-Names)';

        $this->logger->warning(sprintf($message, $type, $name, $type));
    }
}
