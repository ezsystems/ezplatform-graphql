<?php
namespace BD\EzPlatformGraphQLBundle\Schema;

use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentTypeGroup\ContentTypeGroupSchemaWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\ContentType\ContentTypeSchemaWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\FieldDefinition\FieldDefinitionSchemaWorker;
use BD\EzPlatformGraphQLBundle\DomainContent\SchemaWorker\SchemaWorker;
use BD\EzPlatformGraphQLBundle\Schema\SchemaBuilder;
use eZ\Publish\API\Repository\Repository;
use GraphQL\Type\Schema;
use InvalidArgumentException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class SchemaGenerator
{
    /**
     * @var SchemaBuilder[]
     */
    private $schemaBuilders;

    public function __construct(array $schemaBuilders = [])
    {
        $this->schemaBuilders = $schemaBuilders;
    }

    /**
     * @return array
     */
    public function generate()
    {
        $schema = [];
        foreach ($this->schemaBuilders as $builder) {
            $builder->build($schema);
        }
        
        return $schema;
    }

}