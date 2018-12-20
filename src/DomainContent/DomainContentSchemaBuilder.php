<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent;

use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\SchemaWorker;
use EzSystems\EzPlatformGraphQL\Schema\SchemaBuilder;
use eZ\Publish\API\Repository\Repository;

class DomainContentSchemaBuilder implements SchemaBuilder
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var SchemaWorker[]
     */
    private $workers = [];

    public function __construct(Repository $repository, array $workers = [])
    {
        $this->repository = $repository;
        $this->workers = $workers;
    }
    
    public function build(array &$schema)
    {
        $contentTypeService = $this->repository->getContentTypeService();

        $schema['Domain'] = [
            'inherits' => ['Platform'],
            'type' => 'object',
            'config' => [
                'fields' => []
            ]
        ];

        foreach ($contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            $this->runWorkers($schema, [
                'ContentTypeGroup' => $contentTypeGroup
            ]);

            foreach ($contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                $this->runWorkers($schema, [
                    'ContentTypeGroup' => $contentTypeGroup,
                    'ContentType' => $contentType
                ]);

                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    $this->runWorkers($schema, [
                        'ContentTypeGroup' => $contentTypeGroup,
                        'ContentType' => $contentType,
                        'FieldDefinition' => $fieldDefinition
                    ]);

                }
            }
        }
    }

    private function runWorkers(&$schema, $args)
    {
        foreach ($this->workers as $worker) {
            if ($worker->canWork($schema, $args)) {
                $worker->work($schema, $args);
            }
        }
    }

}