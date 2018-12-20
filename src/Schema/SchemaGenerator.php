<?php
namespace EzSystems\EzPlatformGraphQL\Schema;

use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\ContentTypeGroup\ContentTypeGroupSchemaWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\ContentType\ContentTypeSchemaWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\FieldDefinition\FieldDefinitionSchemaWorker;
use EzSystems\EzPlatformGraphQL\DomainContent\SchemaWorker\SchemaWorker;
use EzSystems\EzPlatformGraphQL\Schema\SchemaBuilder;
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