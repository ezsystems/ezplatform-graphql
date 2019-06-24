<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema;

interface Worker
{
    /**
     * Does the work on $schema.
     *
     * @param Builder $schema
     * @param array $args
     */
    public function work(Builder $schema, array $args);

    /**
     * Tests the arguments and schema, and says if the worker can work on that state.
     * It includes testing if the worker was already executed.
     *
     * @param Builder $schema
     * @param array $args
     *
     * @return bool
     */
    public function canWork(Builder $schema, array $args);
}
