<?php
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder;

use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class RelationFieldValueBuilder implements FieldValueBuilder
{
    /**
     * @var NameHelper
     */
    private $nameHelper;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(NameHelper $nameHelper, ContentTypeService $contentTypeService)
    {
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
    }

    public function buildDefinition(FieldDefinition $fieldDefinition)
    {
        $settings = $fieldDefinition->getFieldSettings();
        $constraints = $fieldDefinition->getValidatorConfiguration();

        if (count($settings['selectionContentTypes']) === 1) {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($settings['selectionContentTypes'][0]);
            $type = $this->nameHelper->domainContentName($contentType);
        } else {
            $type = 'DomainContent';
        }

        $isMultiple = 'false';
        if (isset($constraints['RelationListValueValidator']) && $constraints['RelationListValueValidator']['selectionLimit'] !== 1) {
            $isMultiple = 'true';
            $type = "[$type]";
        }

        return [
            'type' => $type,
            'resolve' => sprintf('@=resolver("DomainRelationFieldValue", [field, %s])', $isMultiple)
        ];
    }
}