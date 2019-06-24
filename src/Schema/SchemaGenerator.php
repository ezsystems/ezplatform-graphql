<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema;

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
