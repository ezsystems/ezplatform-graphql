<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Value;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser;

/**
 * A DXP item, combination of a Content and Location
 */
class Item
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser */
    private $locationGuesser;

    public function __construct(LocationGuesser $locationGuesser, ?Location $location = null, ?Content $content = null)
    {
        if ($location === null && $content === null) {
            // @todo find a better exception
            throw new \Exception("Constructing an item requires one of location or content");
        }
        $this->location = $location;
        $this->content = $content;
        $this->locationGuesser = $locationGuesser;
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
            $this->location = $this->locationGuesser->guessLocation($this->content)->getLocation();
        }

        return $this->location;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->getContent()->contentInfo;
    }
}
