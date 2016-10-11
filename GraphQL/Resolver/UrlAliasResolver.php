<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\EzPlatformGraphQLBundle\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

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

    public function __construct(TypeResolver $typeResolver, URLAliasService $urlAliasService)
    {
        $this->urlAliasService = $urlAliasService;
        $this->typeResolver = $typeResolver;
    }

    public function resolveLocationUrlAliases(Location $location, $args)
    {
        return $this->urlAliasService->listLocationAliases(
            $location,
            isset($args['custom']) ? $args['custom'] : false
        );
    }

    public function resolveUrlAliasType(URLAlias $urlAlias)
    {
        switch ($urlAlias->type)
        {
            case URLAlias::LOCATION:
                return $this->typeResolver->resolve('LocationUrlAlias');
            case URLAlias::RESOURCE:
                return $this->typeResolver->resolve('ResourceUrlAlias');
            case URLAlias::VIRTUAL:
                return $this->typeResolver->resolve('VirtualUrlAlias');
        }
    }
}
