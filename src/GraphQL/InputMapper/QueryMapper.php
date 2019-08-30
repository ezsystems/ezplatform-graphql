<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

interface QueryMapper
{
    /**
     * @param array $inputArray
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function mapInputToQuery(array $inputArray);
}
