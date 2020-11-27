<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Item;

class ItemFactory
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser
     */
    private $locationGuesser;
    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\SiteaccessGuesser\SiteaccessGuesser
     */
    private $siteaccessGuesser;

    public function __construct(
        Resolver\LocationGuesser\LocationGuesser $locationGuesser,
        Resolver\SiteaccessGuesser\SiteaccessGuesser $siteaccessGuesser
    ) {
        $this->locationGuesser = $locationGuesser;
        $this->siteaccessGuesser = $siteaccessGuesser;
    }

    public function fromContent(Content $content): Item
    {
        return Item::fromContent(
            $this->locationGuesser,
            $this->siteaccessGuesser,
            $content
        );
    }

    public function fromLocation(Location $location): Item
    {
        return Item::fromLocation(
            $this->locationGuesser,
            $this->siteaccessGuesser,
            $location
        );
    }
}
