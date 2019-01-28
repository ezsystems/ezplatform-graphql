<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 21/09/2018
 * Time: 16:50
 */

namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use eZ\Publish\API\Repository\Values\Content\Query;
use InvalidArgumentException;

class SearchQueryMapper
{
    private $criteriaMappers;

    public function __construct($criteriaMappers)
    {
        $this->criteriaMappers = $criteriaMappers;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function mapInputToQuery(array $inputArray) : Query
    {
        $query = new Query();
        $criteria = [];

        foreach ($inputArray as $inputField => $inputValue) {
            if ($criteriaMapper = $this->existsCriteriaMapperForField($inputField)) {
                $criteria = array_merge($criteria, $this->criteriaMappers[$inputField]->map($inputValue));
            }

            if ($inputField == 'sortBy') {
                $query->sortClauses = $this->mapSortByPart($inputValue);

                // remove null entries left out because of sort direction
                $query->sortClauses = array_filter($query->sortClauses);
            }
        }

        if (count($criteria) === 0) {
            return $query;
        }

        if (count($criteria)) {
            $query->filter = count($criteria) > 1 ? new Query\Criterion\LogicalAnd($criteria) : $criteria[0];
        }

        return $query;
    }

    /**
     * @param string $inputField
     * @return bool
     */
    private function existsCriteriaMapperForField(string $inputField) : bool
    {
        return isset($this->criteriaMappers[$inputField]);
    }

    /**
     * @param $inputValue
     * @return array
     */
    private function mapSortByPart($inputValue) : array
    {
        return array_map(
            function ($sortClauseClass) {

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

                return $lastSortClause = new $sortClauseClass;
            },
            $inputValue
        );
    }

}
