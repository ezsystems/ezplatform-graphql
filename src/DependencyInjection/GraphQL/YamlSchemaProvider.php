<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL;

use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\Map\UploadMap;

/**
 * Provides schema definitions.
 */
class YamlSchemaProvider implements SchemaProvider
{
    const PLATFORM_SCHEMA_PATH = 'ezplatform/';
    const PLATFORM_SCHEMA_FILE = self::PLATFORM_SCHEMA_PATH . 'Domain.types.yaml';
    const PLATFORM_MUTATION_FILE = self::PLATFORM_SCHEMA_PATH . 'DomainContentMutation.types.yaml';
    const APP_QUERY_SCHEMA_FILE = 'Query.types.yaml';
    const APP_MUTATION_SCHEMA_FILE = 'Mutation.types.yaml';

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

    public function getSchemaConfiguration()
    {
        return [
            'query' => $this->getQuerySchema(),
            'mutation' => $this->getMutationSchema(),
            'resolver_maps' => [UploadMap::class],
            'types' => ['UntypedContent'],
        ];
    }

    private function getQuerySchema()
    {
        if (file_exists($this->getAppQuerySchema())) {
            return 'Query';
        } elseif (file_exists($this->getPlatformQuerySchema())) {
            return 'Domain';
        } else {
            return 'Platform';
        }
    }

    private function getMutationSchema()
    {
        if (file_exists($this->getAppMutationSchemaFile())) {
            return 'Mutation';
        } elseif (file_exists($this->getPlatformMutationSchema())) {
            return 'DomainContentMutation';
        } else {
            return null;
        }
    }

    private function getAppQuerySchema()
    {
        return $this->root . self::APP_QUERY_SCHEMA_FILE;
    }

    private function getAppMutationSchemaFile()
    {
        return $this->root . self::APP_MUTATION_SCHEMA_FILE;
    }

    private function getPlatformQuerySchema()
    {
        return $this->root . self::PLATFORM_SCHEMA_FILE;
    }

    private function getPlatformMutationSchema()
    {
        return $this->root . self::PLATFORM_MUTATION_FILE;
    }
}
