<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query;

class Text implements SearchCriterion
{
    public function map($value): array
    {
        $criteria = [];

        foreach ($value as $text) {
            $criteria[] = new Query\Criterion\FullText($text);
        }

        return $criteria;
    }
}
