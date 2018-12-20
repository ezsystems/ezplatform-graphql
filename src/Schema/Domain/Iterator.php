<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain;

use EzSystems\EzPlatformGraphQL\Schema\Builder;
use Generator;

/**
 * Iterates on a domain, and returns sets of items a schema can be generated from.
 */
interface Iterator
{
    /**
     * Performs one-time initializations on the schema.
     * @param Builder $schema
     * @return
     */
    public function init(Builder $schema);

    /**
     * Returns set of items from the domain.
     *
     * @return \Generator a generator yielding the items
     */
    public function iterate(): Generator;
}