<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use InvalidArgumentException;

final class SearchQueryMapper implements QueryMapper
{
    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\InputMapper\ContentCollectionFilterBuilder
     */
    private $filterBuilder;

    public function __construct(ContentCollectionFilterBuilder $filterBuilder)
    {
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\LocationQuery
     */
    public function mapInputToLocationQuery(array $inputArray): LocationQuery
    {
        $query = new LocationQuery();
        $this->mapInput($query, $inputArray);

        return $query;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function mapInputToQuery(array $inputArray): Query
    {
        $query = new Query();
        $this->mapInput($query, $inputArray);

        return $query;
    }

    private function mapInput($query, array $inputArray): void
    {
        if (isset($inputArray['offset'])) {
            $query->offset = $inputArray['offset'];
        }
        if (isset($inputArray['limit'])) {
            $query->limit = $inputArray['limit'];
        }
        $criteria = [$this->filterBuilder->buildFilter()];

        if (isset($inputArray['ContentTypeIdentifier'])) {
            $criteria[] = new Query\Criterion\ContentTypeIdentifier($inputArray['ContentTypeIdentifier']);
        }

        if (isset($inputArray['Text'])) {
            $criteria[] = new Query\Criterion\FullText($inputArray['Text']);
        }

        if (isset($inputArray['Field'])) {
            if (isset($inputArray['Field']['target'])) {
                $criteria[] = $this->mapInputToFieldCriterion($inputArray['Field']);
            } else {
                $criteria = array_merge(
                    $criteria,
                    array_map(
                        function ($input) {
                            return $this->mapInputToFieldCriterion($input);
                        },
                        $inputArray['Field']
                    )
                );
            }
        }

        if (isset($inputArray['ParentLocationId'])) {
            $criteria[] = new Query\Criterion\ParentLocationId($inputArray['ParentLocationId']);
        }

        $criteria = array_merge($criteria, $this->mapDateMetadata($inputArray, 'Modified'));
        $criteria = array_merge($criteria, $this->mapDateMetadata($inputArray, 'Created'));

        if (isset($inputArray['sortBy'])) {
            $query->sortClauses = array_map(
                function ($sortClauseClass) {
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
                $inputArray['sortBy']
            );
            // remove null entries left out because of sort direction
            $query->sortClauses = array_filter($query->sortClauses);
        }

        if (count($criteria) === 0) {
            return;
        }

        $query->filter = new Query\Criterion\LogicalAnd($criteria);
    }

    /**
     * @param $dateMetadata
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata[]
     */
    private function mapDateMetadata(array $queryArg = [], $dateMetadata)
    {
        if (!isset($queryArg[$dateMetadata]) || !is_array($queryArg[$dateMetadata])) {
            return [];
        }

        $targetMap = [
            'Created' => Query\Criterion\DateMetadata::CREATED,
            'Modified' => Query\Criterion\DateMetadata::MODIFIED,
        ];

        if (!isset($targetMap[$dateMetadata])) {
            return [];
        }

        $dateOperatorsMap = [
            'on' => Query\Criterion\Operator::EQ,
            'before' => Query\Criterion\Operator::LTE,
            'after' => Query\Criterion\Operator::GTE,
        ];

        $criteria = [];
        foreach ($queryArg[$dateMetadata] as $operator => $dateString) {
            if (!isset($dateOperatorsMap[$operator])) {
                continue;
            }

            $criteria[] = new Query\Criterion\DateMetadata(
                $targetMap[$dateMetadata],
                $dateOperatorsMap[$operator],
                strtotime($dateString)
            );
        }

        return $criteria;
    }

    private function mapInputToFieldCriterion($input)
    {
        $operators = ['in', 'eq', 'like', 'contains', 'between', 'lt', 'lte', 'gt', 'gte'];
        foreach ($operators as $opString) {
            if (isset($input[$opString])) {
                $value = $input[$opString];
                $operator = constant('eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator::' . strtoupper($opString));
            }
        }

        if (!isset($operator)) {
            throw new InvalidArgumentException('Unspecified operator');
        }

        return new Query\Criterion\Field($input['target'], $operator, $value);
    }
}
