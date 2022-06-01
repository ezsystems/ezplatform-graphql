<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\Mapper;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\ImageAsset;

interface ImageAssetMapperStrategyInterface
{
    public function canProcess(ImageAsset\Value $value): bool;

    public function process(ImageAsset\Value $value): Field;
}
