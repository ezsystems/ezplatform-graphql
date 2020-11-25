<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\Exception;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

class MultipleValidLocationsException extends \Exception
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    private $locations = [];

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    private $content;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     */
    public function __construct(Content $content, array $locations)
    {
        parent::__construct(
            sprintf(
                'Could not determine which location to return for content with id %s. Possible candidates: %s)',
                $content->id,
                implode(',', array_column($locations, 'pathString'))
            )
        );
        $this->locations = $locations;
        $this->content = $content;
    }

    /**
     * @return Location[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
