<?php
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