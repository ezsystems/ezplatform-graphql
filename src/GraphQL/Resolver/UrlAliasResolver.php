<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Item;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
class UrlAliasResolver
{
    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    private $urlAliasService;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    private $urlGenerator;
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService
     */
    private $siteaccessService;

    public function __construct(
        TypeResolver $typeResolver,
        URLAliasService $urlAliasService,
        LocationService $locationService,
        ConfigResolverInterface $configResolver,
        UrlAliasGenerator $urlGenerator,
        SiteAccessServiceInterface $siteAccessService)
    {
        $this->urlAliasService = $urlAliasService;
        $this->typeResolver = $typeResolver;
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
        $this->urlGenerator = $urlGenerator;
        $this->siteaccessService = $siteAccessService;
    }

    public function resolveLocationUrlAliases(Location $location, $args)
    {
        return $this->urlAliasService->listLocationAliases(
            $location,
            isset($args['custom']) ? $args['custom'] : false
        );
    }

    public function resolveUrlAliasType(URLAlias $urlAlias): string
    {
        switch ($urlAlias->type) {
            case URLAlias::LOCATION:
                return $this->typeResolver->resolve('LocationUrlAlias');
            case URLAlias::RESOURCE:
                return $this->typeResolver->resolve('ResourceUrlAlias');
            case URLAlias::VIRTUAL:
                return $this->typeResolver->resolve('VirtualUrlAlias');
        }
    }

    public function resolveLocationUrlAlias(Location $location): ?string
    {
        return $this->urlGenerator->generate($location, [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Resolves the URL alias for an item, taking into account the item's siteaccess.
     */
    public function resolveItemUrlAlias(Item $item): ?string
    {
        if ($item->getSiteaccess() === $this->siteaccessService->getCurrent()) {
            $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        } else {
            $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;
        }

        return $this->urlGenerator->generate(
            $item->getLocation(),
            ['siteaccess' => $item->getSiteaccess()->name],
            $referenceType
        );
    }
}
