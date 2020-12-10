<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * Guesses locations for a site by filtering out a provided list.
 */
class FilterLocationGuesser implements LocationGuesser
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationFilter[]
     */
    private $filters;

    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationProvider
     */
    private $provider;

    public function __construct(LocationProvider $provider, array $filters)
    {
        $this->provider = $provider;
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function guessLocation(Content $content): LocationGuess
    {
        $locationList = $this->provider->getLocations($content);

        if (!$locationList->hasOneLocation()) {
            foreach ($this->filters as $filter) {
                $filter->filter($content, $locationList);
                if ($locationList->hasOneLocation()) {
                    return new LocationGuess($content, $locationList->getLocations());
                }
            }
        }

        return new LocationGuess($content, $locationList->getLocations());
    }
}
