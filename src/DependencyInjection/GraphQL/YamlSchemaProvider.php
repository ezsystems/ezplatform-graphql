<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL;

/**
 * Provides schema definitions.
 */
class YamlSchemaProvider implements SchemaProvider
{
    const PLATFORM_SCHEMA_PATH = 'ezplatform/';
    const PLATFORM_SCHEMA_FILE = self::PLATFORM_SCHEMA_PATH . 'Domain.types.yml';
    const APP_QUERY_SCHEMA_FILE = 'Query.types.yml';
    const APP_MUTATION_SCHEMA_FILE = 'Mutation.types.yml';

    /**
     * The path to the graphql configuration root.
     *
     * @var string
     */
    private $root;

    public function __construct($graphQLConfigRoot)
    {
        $this->root = rtrim($graphQLConfigRoot, '/') . '/';
    }

    public function getQuerySchema()
    {
        if (file_exists($this->getAppQuerySchema())) {
            return 'Query';
        } else if (file_exists($this->getPlatformQuerySchema())) {
            return 'Domain';
        } else {
            return 'Platform';
        }
    }

    public function getMutationSchema()
    {
        return file_exists(self::APP_MUTATION_SCHEMA_FILE)
            ? 'Mutation'
            : null;
    }

    private function getAppQuerySchema()
    {
        return $this->root . self::APP_QUERY_SCHEMA_FILE;
    }

    private function getPlatformQuerySchema()
    {
        return $this->root . self::PLATFORM_SCHEMA_FILE;
    }
}
