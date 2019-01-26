<?php

namespace BD\EzPlatformGraphQLBundle\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class ContentTypeIdentifier implements SearchCriterion
{
    public function resolve($value): CriterionInterface
    {
        if (!is_string($value) && !is_array($value)) {
            throw new InvalidArgumentException($value, 'value needs to be an string or an array.');
        }

        return new Content\Query\Criterion\ContentTypeIdentifier($value);
    }
}
