<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\SiteaccessGuesser;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use EzSystems\EzPlatformAdminUi\Specification\SiteAccess\IsAdmin;
use EzSystems\EzPlatformGraphQL\Exception\NoValidSiteaccessException;

class SiteaccessGuesser
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface
     */
    private $provider;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface
     */
    private $siteAccessService;
    /**
     * @var array
     */
    private $siteAccessGroups;

    public function __construct(
        SiteAccess\SiteAccessServiceInterface $siteAccessService,
        SiteAccessProviderInterface $provider,
        ConfigResolverInterface $configResolver,
        array $siteAccessGroups
    ) {
        $this->provider = $provider;
        $this->configResolver = $configResolver;
        $this->siteAccessService = $siteAccessService;
        $this->siteAccessGroups = $siteAccessGroups;
    }

    /**
     * @throws \EzSystems\EzPlatformGraphQL\Exception\NoValidSiteaccessException
     */
    public function guessForLocation(Location $location): SiteAccess
    {
        // Test if the location is part of the current tree root, as it is the most likely
        $treeRootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        if ($this->isInSubtree($location, $treeRootLocationId)) {
            return $this->siteAccessService->getCurrent();
        }

        // we won't look into siteaccesses that don't use the same repository
        $currentRepository = $this->configResolver->getParameter('repository');

        /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess[] $saList */
        $matchingSiteaccessRootDepth = 0;
        $saList = iterator_to_array($this->provider->getSiteAccesses());

        foreach ($saList as $siteaccess) {
            $repository = $this->configResolver->getParameter('repository', 'ezsettings', $siteaccess->name);

            if ($repository !== $currentRepository) {
                continue;
            }

            if ($this->isAdminSiteaccess($siteaccess)) {
                continue;
            }

            $treeRootLocationId = $this->configResolver->getParameter('content.tree_root.location_id', 'ezsettings', $siteaccess->name);
            if (($rootDepth = $this->isInSubtree($location, $treeRootLocationId)) !== false) {
                if ($rootDepth > $matchingSiteaccessRootDepth) {
                    $matchingSiteaccess = $siteaccess;
                    $matchingSiteaccessRootDepth = $rootDepth;
                }
            }
        }

        if (!isset($matchingSiteaccess)) {
            throw new NoValidSiteaccessException($location);
        }

        return $matchingSiteaccess;
    }

    /**
     * Tests if $location is part of a subtree, and returns the root depth.
     *
     * @return int|false The root depth (used to select the deepest, most specific tree root), false if it isn't part of that subtree.
     */
    private function isInSubtree(Location $location, int $treeRootLocationId)
    {
        return array_search($treeRootLocationId, $location->path);
    }

    private function isAdminSiteaccess(SiteAccess $siteaccess)
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($siteaccess);
    }
}
