<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\DependencyInjection\GraphQL;

/**
 * Provides schema definitions.
 */
interface SchemaProvider
{
    /**
     * Returns the GraphQL type of the eZ Platform Schema. Example: Query.
     * @return string
     */
    public function getQuerySchema();

    /**
     * Returns the GraphQL mutation type of the eZ Platform Schema. Example: Mutation.
     * @return string
     */
    public function getMutationSchema();
}
