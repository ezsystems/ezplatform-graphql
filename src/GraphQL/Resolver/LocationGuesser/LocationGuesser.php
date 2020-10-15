<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

interface LocationGuesser
{
    /**
     * Given a Content item, returns a location.
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \EzSystems\EzPlatformGraphQL\Exception\NoValidLocationFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \EzSystems\EzPlatformGraphQL\Exception\MultiplePossibleLocationsException
     */
    public function guessLocation(Content $content): LocationGuess;
}
