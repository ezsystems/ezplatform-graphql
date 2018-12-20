<?php
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
            $args = ['ContentTypeGroup' => $contentTypeGroup];
            yield $args;

            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                $args['ContentType'] = $contentType;
                yield $args;

                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    $args['FieldDefinition'] = $fieldDefinition;
                    yield $args;
                }
            }
        }
    }
}