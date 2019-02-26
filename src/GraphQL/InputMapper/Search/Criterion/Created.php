<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;

class Created extends DateMetadata
{
    const TARGET = Query\Criterion\DateMetadata::CREATED;

    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $this->visitCriterion($queryBuilder, $value, self::TARGET);
    }
}
