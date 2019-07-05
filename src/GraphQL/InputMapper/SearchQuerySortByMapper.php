<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use eZ\Publish\API\Repository\Values\Content\Query;

class SearchQuerySortByMapper
{
    /**
     * @param string[] $sortInput
     *
     * @return \eZ\Publish\API\Repository\Values\URL\Query\SortClause[]
     */
    public function mapInputToSortClauses(array $sortInput)
    {
        $sortClauses = array_map(
            function (string $sortClauseClass) {
                /** @var Query\SortClause $lastSortClause */
                static $lastSortClause;

                if ($sortClauseClass === Query::SORT_DESC) {
                    if (!$lastSortClause instanceof Query\SortClause) {
                        return null;
                    }

                    $lastSortClause->direction = $sortClauseClass;

                    return null;
                }

                if (!class_exists($sortClauseClass)) {
                    return null;
                }

                if (!in_array(Query\SortClause::class, class_parents($sortClauseClass))) {
                    return null;
                }

                return $lastSortClause = new $sortClauseClass();
            },
            $sortInput
        );

        return array_filter($sortClauses);
    }
}
