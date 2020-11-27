<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Exception;

use Exception;
use eZ\Publish\API\Repository\Values\Content\Location;

class NoValidSiteaccessException extends Exception
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    public function __construct(Location $location)
    {
        parent::__construct("Could not find a suitable siteaccess for the location with id $location->id");
        $this->location = $location;
    }
}
