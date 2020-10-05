<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Builds the base query used to retrieve collections.
 * @internal
 */
class ContentCollectionFilterBuilder
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(ConfigResolverInterface $configResolver, Repository $repository)
    {
        $this->configResolver = $configResolver;
        $this->repository = $repository;
    }

    /**
     * Returns a criterion to be added as a global 'and' to a query's filters.
     */
    public function buildFilter(): Criterion
    {
        $treeRootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        $rootLocation = $this->repository->getLocationService()->loadLocation($treeRootLocationId);

        $includedSubtrees = [$rootLocation->pathString];

        foreach ($this->configResolver->getParameter('content.tree_root.excluded_uri_prefixes') as $uriPrefix) {
            $urlAlias = $this->repository->getURLAliasService()->lookup($uriPrefix);
            if ($urlAlias->type === URLAlias::LOCATION) {
                $includedSubtrees[] = $this->repository->getLocationService()->loadLocation($urlAlias->destination);
            }
        }

        return new SubTree($includedSubtrees);
    }
}
