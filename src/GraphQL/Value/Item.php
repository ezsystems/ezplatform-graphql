<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Value;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use EzSystems\EzPlatformGraphQL\Exception\NoValidLocationsException;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\SiteaccessGuesser\SiteaccessGuesser;
use Overblog\GraphQLBundle\Error\UserError;

/**
 * A DXP item, combination of a Content and Location.
 */
class Item
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser */
    private $locationGuesser;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteaccess;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\SiteaccessGuesser\SiteaccessGuesser */
    private $siteaccessGuesser;

    private function __construct(LocationGuesser $locationGuesser, SiteaccessGuesser $siteaccessGuesser, ?Location $location = null, ?Content $content = null)
    {
        if ($location === null && $content === null) {
            throw new InvalidArgumentException('content or location', 'one of content or location is required');
        }
        $this->location = $location;
        $this->content = $content;
        $this->locationGuesser = $locationGuesser;
        $this->siteaccessGuesser = $siteaccessGuesser;
    }

    public function getContent(): Content
    {
        if ($this->content === null) {
            $this->content = $this->location->getContent();
        }

        return $this->content;
    }

    public function getLocation(): Location
    {
        if ($this->location === null) {
            try {
                $this->location = $this->locationGuesser->guessLocation($this->content)->getLocation();
            } catch (NoValidLocationsException $e) {
                throw new UserError($e->getMessage(), 0, $e);
            }
        }

        return $this->location;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->getContent()->contentInfo;
    }

    public static function fromContent(LocationGuesser $locationGuesser, SiteaccessGuesser $siteaccessGuesser, Content $content): self
    {
        return new self($locationGuesser, $siteaccessGuesser, null, $content);
    }

    public static function fromLocation(LocationGuesser $locationGuesser, SiteaccessGuesser $siteaccessGuesser, Location $location): self
    {
        return new self($locationGuesser, $siteaccessGuesser, $location, null);
    }

    public function getSiteaccess(): SiteAccess
    {
        if ($this->siteaccess === null) {
            $this->siteaccess = $this->siteaccessGuesser->guessForLocation($this->getLocation());
        }

        return $this->siteaccess;
    }
}
