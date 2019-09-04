<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\FieldDefinition;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Worker\BaseWorker;
use EzSystems\EzPlatformGraphQL\Schema\Worker;
use EzSystems\EzPlatformGraphQL\Search\SearchFeatures;

/**
 * Adds the field definition, if it is searchable, as a filter on the type's collection.
 */
class AddFieldDefinitionToCollectionFilters extends BaseWorker implements Worker
{
    /**
     * @var SearchFeatures
     */
    private $searchFeatures;

    public function __construct(SearchFeatures $searchFeatures)
    {
        $this->searchFeatures = $searchFeatures;
    }

    public function work(Builder $schema, array $args)
    {
        $schema->addFieldToType(
            $this->filterType($args),
            new Builder\Input\Field(
                $this->fieldDefinitionField($args),
                $this->getFilterType($args['FieldDefinition']),
                ['description' => 'Filter content based on the ' . $args['FieldDefinition']->identifier . ' field']
            )
        );
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            & isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && $this->searchFeatures->supportsFieldCriterion($args['FieldDefinition']);
    }

    /**
     * @param array $args
     *
     * @return string
     */
    protected function filterType(array $args): string
    {
        return $this->getNameHelper()->filterType($args['ContentType']);
    }

    private function isSearchable(FieldDefinition $fieldDefinition): bool
    {
        return $fieldDefinition->isSearchable
            // should only be verified if legacy is the current search engine
            && $this->converterRegistry->getConverter($fieldDefinition->fieldTypeIdentifier)->getIndexColumn() !== false;
    }

    private function getFilterType(FieldDefinition $fieldDefinition): string
    {
        switch ($fieldDefinition->fieldTypeIdentifier) {
            case 'ezboolean':
                return 'Boolean';
            default:
                return 'String';
        }
    }

    private function fieldDefinitionField(array $args): string
    {
        return $this->getNameHelper()->fieldDefinitionField($args['FieldDefinition']);
    }
}
