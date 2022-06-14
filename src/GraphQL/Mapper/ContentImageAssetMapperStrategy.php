<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\GraphQL\GraphQL\Mapper;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\FieldType\ImageAsset;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;

final class ContentImageAssetMapperStrategy implements ImageAssetMapperStrategyInterface
{
    /* @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper */
    private $assetMapper;

    /** @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader */
    private $contentLoader;

    public function __construct(
        ImageAsset\AssetMapper $assetMapper,
        ContentLoader $contentLoader
    ) {
        $this->assetMapper = $assetMapper;
        $this->contentLoader = $contentLoader;
    }

    public function canProcess(ImageAsset\Value $value): bool
    {
        return $value->source === null;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException|
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function process(ImageAsset\Value $value): Field
    {
        $assetField = $this->assetMapper->getAssetField(
            $this->contentLoader->findSingle(new Criterion\ContentId($value->destinationContentId))
        );

        if (empty($assetField->value->alternativeText)) {
            $assetField->value->alternativeText = $value->alternativeText;
        }

        return $assetField;
    }
}
