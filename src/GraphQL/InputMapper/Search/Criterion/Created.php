<?php

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\Search\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query;

class Created extends DateMetadata
{
    const TARGET = Query\Criterion\DateMetadata::CREATED;
}
