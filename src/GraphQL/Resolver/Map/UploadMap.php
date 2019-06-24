<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver\Map;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Overblog\GraphQLBundle\Upload\Type\GraphQLUploadType;

class UploadMap extends ResolverMap
{
    protected function map()
    {
        return [
            'FileUpload' => [self::SCALAR_TYPE => function () { return new GraphQLUploadType(); }],
        ];
    }
}
