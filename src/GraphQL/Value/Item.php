<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Value;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * A DXP item, combination of a Content and Location
 */
class Item
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
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
        return $this->location;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->getContent()->contentInfo;
    }
}
