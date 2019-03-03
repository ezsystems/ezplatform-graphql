<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criteria;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\QueryBuilder;

class Modified extends DateMetadata
{
    const TARGET = Query\Criterion\DateMetadata::MODIFIED;

    public function visit(QueryBuilder $queryBuilder, $value): void
    {
        $this->visitCriterion($queryBuilder, $value, self::TARGET);
    }
}
