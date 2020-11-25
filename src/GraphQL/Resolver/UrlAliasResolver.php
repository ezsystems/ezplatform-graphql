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
use Overblog\GraphQLBundle\Resolver\TypeResolver;

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

    public function __construct(
        TypeResolver $typeResolver,
        URLAliasService $urlAliasService,
        LocationService $locationService,
        ConfigResolverInterface $configResolver)
    {
        $this->urlAliasService = $urlAliasService;
        $this->typeResolver = $typeResolver;
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
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
        $aliases = $this->urlAliasService->listLocationAliases($location, false);

        if (empty($aliases)) {
            return null;
        }

        $path = $aliases[0]->path;
        $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
        if ($rootLocationId !== null) {
            $pathPrefix = $this->getPathPrefixByRootLocationId($rootLocationId);
            // "/" cannot be considered as a path prefix since it's root, so we ignore it.
            if ($pathPrefix !== '/' && ($path === $pathPrefix || mb_stripos($path, $pathPrefix . '/') === 0)) {
                $path = mb_substr($path, mb_strlen($pathPrefix));
            }
        }

        return $path;
    }

    public function getPathPrefixByRootLocationId(int $rootLocationId): string
    {
        // @todo this might be heavy, as it will be executed for each url alias that is generated
        return $this->urlAliasService
                ->reverseLookup($this->locationService->loadLocation($rootLocationId))
                ->path;
    }

    /**
     * Checks if passed URI has an excluded prefix, when a root location is defined.
     */
    public function isUriPrefixExcluded(string $uri): bool
    {
        $excludedUriPrefixes = $this->configResolver->getParameter('content.tree_root.excluded_uri_prefixes');
        foreach ($excludedUriPrefixes as $excludedPrefix) {
            $excludedPrefix = '/' . trim($excludedPrefix, '/');
            if (mb_stripos($uri, $excludedPrefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
