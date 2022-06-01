<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\Mapper\ImageAssetMapperStrategyInterface;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;

/**
 * @internal
 */
class ImageAssetFieldResolver
{
    /* @var \Ibexa\GraphQL\Mapper\ImageAssetMapperStrategyInterface[] */
    private $strategies;

    /**
     * @param iterable<ImageAssetMapperStrategyInterface> $strategies
     */
    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            if ($strategy instanceof ImageAssetMapperStrategyInterface) {
                $this->addStrategy($strategy);
            }
        }
    }

    private function addStrategy(ImageAssetMapperStrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    public function resolveDomainImageAssetFieldValue(Field $field): ?Field
    {
        $destinationContentId = $field->value->destinationContentId;

        if ($destinationContentId === null) {
            return null;
        }

        foreach ($this->strategies as $strategy) {
            if ($strategy->canProcess($field->value)) {
                $assetField = $strategy->process($field->value);

                return Field::fromField($assetField);
            }
        }

        return null;
    }
}
