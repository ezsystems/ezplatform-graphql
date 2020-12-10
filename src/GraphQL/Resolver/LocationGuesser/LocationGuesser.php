<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\Values\Content\Content;

interface LocationGuesser
{
    /**
     * Tries to guess a valid location for a content item.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return LocationGuess
     */
    public function guessLocation(Content $content): LocationGuess;
}
