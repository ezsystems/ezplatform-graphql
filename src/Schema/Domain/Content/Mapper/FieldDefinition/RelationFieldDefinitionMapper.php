<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;

class RelationFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    /**
     * @var NameHelper
     */
    private $nameHelper;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    ) {
        parent::__construct($innerMapper);
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }
        $settings = $fieldDefinition->getFieldSettings();

        $type = 'Item';
        if (count($settings['selectionContentTypes']) === 1) {
            try {
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
                    $settings['selectionContentTypes'][0]
                );
                $type = $this->nameHelper->itemName($contentType);
            } catch (NotFoundException $e) {
                // Nothing to do
            }
        }

        if ($this->isMultiple($fieldDefinition)) {
            $type = "[$type]";
        }

        return $type;
    }

    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueResolver($fieldDefinition);
        }

        $isMultiple = $this->isMultiple($fieldDefinition) ? 'true' : 'false';

        return sprintf('@=resolver("RelationFieldValue", [field, %s])', $isMultiple);
    }

    protected function canMap(FieldDefinition $fieldDefinition)
    {
        return in_array($fieldDefinition->fieldTypeIdentifier, ['ezobjectrelation', 'ezobjectrelationlist']);
    }

    /**
     * Not implemented since we don't use it (canMap is overridden).
     */
    public function getFieldTypeIdentifier(): string
    {
        return '';
    }

    private function isMultiple(FieldDefinition $fieldDefinition)
    {
        $constraints = $fieldDefinition->getValidatorConfiguration();

        return isset($constraints['RelationListValueValidator'])
            && $constraints['RelationListValueValidator']['selectionLimit'] !== 1;
    }
}
