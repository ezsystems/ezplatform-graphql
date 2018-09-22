<?php
namespace BD\EzPlatformGraphQLBundle\DomainContent;

use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentTypeGroup\ContentTypeGroupSchemaWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentType\ContentTypeSchemaWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\FieldDefinition\FieldDefinitionSchemaWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use eZ\Publish\API\Repository\Repository;
use InvalidArgumentException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class RepositoryDomainGenerator
{
    /**
     * @var SchemaWorker[]
     */
    private $workers = [];
    
    public function __construct()
    {
    }

    public function addWorker(SchemaWorker $worker)
    {
        $this->workers[] = $worker;
    }

    /**
     * @param Repository $repository
     *
     * @return array
     */
    public function generateFromRepository(Repository $repository)
    {
        $contentTypeService = $repository->getContentTypeService();

        $schema = [
            'Domain' => [
                'type' => 'object',
                'config' => [
                    'fields' => []
                ]
            ]
        ];

        foreach ($contentTypeService->loadContentTypeGroups() as $contentTypeGroup)
        {
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

        return $schema;
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