<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Exception;

use eZ\Publish\API\Repository\Values\Content\Location;

class MultiplePossibleLocationsException extends \Exception
{
    /**
     * @var Location[]
     */
    private $locations = [];

    /**
     * @param Location[] $locations
     */
    public function __construct(array $locations)
    {
        $this->locations = $locations;
        parent::__construct(
            sprintf(
                'Could not determine which location to return for content with id %s (locations: %s)',
                $locations[0]->contentId,
                implode(',', array_map(function (Location $location) { return $location->pathString; }, $locations))
            )
        );
    }

    public function getLocations(): array
    {
        return $this->locations;
    }
}
