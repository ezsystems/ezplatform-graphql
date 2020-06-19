<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\DataLoader;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * @internal
 */
interface LocationLoader
{
    /**
     * Loads a list of locations given a Query Criterion.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function find(LocationQuery $query): array;

    /**
     * Loads a single content item given a Query Criterion.
     *
     * @param string $id a location id
     */
    public function findById($id): Location;

    /**
     * Loads a single content item given a Query Criterion.
     *
     * @param string $remoteId A location remote id
     */
    public function findByRemoteId($remoteId): Location;

    /**
     * @param string $remoteId A location remote id
     */
    public function findByUrlAlias(string $urlAlias): Location;

    /**
     * Counts the results of a query.
     *
     * @return int
     */
    public function count(LocationQuery $query);
}
