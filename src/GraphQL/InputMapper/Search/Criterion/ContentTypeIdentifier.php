<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

//@todo remove it, not needed anymore
class ContentTypeIdentifier implements SearchCriterion
{
    public function map($value): array
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException($value, 'value needs to be an string');
        }

        return [new Content\Query\Criterion\ContentTypeIdentifier($value)];
    }
}
