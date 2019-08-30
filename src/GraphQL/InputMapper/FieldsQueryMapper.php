<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\InputMapper;

use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;

/**
 * Pre-processes the input to change fields passed using their identifier to the Field input key.
 */
class FieldsQueryMapper implements QueryMapper
{
    /**
     * @var QueryMapper
     */
    private $innerMapper;
    /**
     * @var ContentTypeLoader
     */
    private $contentTypeLoader;

    public function __construct(ContentTypeLoader $contentTypeLoader, QueryMapper $innerMapper)
    {
        $this->innerMapper = $innerMapper;
        $this->contentTypeLoader = $contentTypeLoader;
    }

    /**
     * @param array $inputArray
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    public function mapInputToQuery(array $inputArray)
    {
        if (isset($inputArray['ContentTypeIdentifier']) && isset($inputArray['fieldsFilters'])) {
            $contentType = $this->contentTypeLoader->loadByIdentifier($inputArray['ContentTypeIdentifier']);
            $fieldsArgument = [];

            foreach ($inputArray['fieldsFilters'] as $fieldDefinitionIdentifier => $value) {
                if (($fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier)) === null) {
                    continue;
                }

                if (!$fieldDefinition->isSearchable) {
                    continue;
                }

                $fieldFilter = $this->buildFieldFilter($fieldDefinitionIdentifier, $value);
                if ($fieldFilter !== null) {
                    $fieldsArgument[] = $fieldFilter;
                }
            }

            $inputArray['Fields'] = $fieldsArgument;
        }

        return $this->innerMapper->mapInputToQuery($inputArray);
    }

    private function buildFieldFilter($fieldDefinitionIdentifier, $value)
    {
        if (is_array($value) && count($value) === 1) {
            $value = $value[0];
        }
        $operator = 'eq';

        // @todo if 3 items, and first item is 'between', use next two items as value
        if (is_array($value)) {
            $operator = 'in';
        } elseif (is_string($value)) {
            if ($value[0] === '~') {
                $operator = 'like';
                $value = substr($value, 1);
                if (strpos($value, '%') === false) {
                    $value = "%$value%";
                }
            } elseif ($value[0] === '<') {
                $value = substr($value, 1);
                if ($value[0] === '=') {
                    $operator = 'lte';
                    $value = substr($value, 2);
                } else {
                    $operator = 'lt';
                    $value = substr($value, 1);
                }
            } elseif ($value[0] === '<') {
                $value = substr($value, 1);
                if ($value[0] === '=') {
                    $operator = 'gte';
                    $value = substr($value, 2);
                } else {
                    $operator = 'gt';
                    $value = substr($value, 1);
                }
            }
        }

        return ['target' => $fieldDefinitionIdentifier, $operator => trim($value)];
    }
}
