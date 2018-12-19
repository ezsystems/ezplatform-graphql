<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\ContentType;

use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\BaseWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * Adds a content type to the content type identifiers list (ContentTypeIdentifier)
 */
class AddContentTypeToContentTypeIdentifierList extends BaseWorker implements SchemaWorker
{
    public function work(array &$schema, array $args)
    {
        $contentType = $args['ContentType'];
        $descriptions = $contentType->getDescriptions();

        if (!isset($schema['ContentTypeIdentifier'])) {
            $this->initType($schema);
        }

        $schema
            ['ContentTypeIdentifier']
            ['config']['values']
            [$contentType->identifier] = [
                'description' => isset($descriptions['eng-GB']) ? $descriptions['eng-GB'] : 'No description available',
            ];
        }

    private function initType(&$schema)
    {
        $schema['ContentTypeIdentifier'] = ['type' => 'enum'];
    }

    public function canWork(array $schema, array $args)
    {
        $canWork =
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && !isset($schema['ContentTypeIdentifier']['config']['values'][$args['ContentType']->identifier]);

        return $canWork;
    }
}