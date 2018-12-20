<?php
namespace EzSystems\EzPlatformGraphQL\DomainContent\FieldValueBuilder;

use EzSystems\EzPlatformGraphQL\DomainContent\NameHelper;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

class ImageAssetFieldValueBuilder implements FieldValueBuilder
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

        if (isset($settings['selectionContentTypes']) && count($settings['selectionContentTypes']) === 1) {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($settings['selectionContentTypes'][0]);
            $type = $this->nameHelper->domainContentName($contentType);
        } else {
            $type = 'DomainContent';
        }

        $isMultiple = false;
        if (isset($constraints['RelationListValueValidator']['selectionLimit']) && $constraints['RelationListValueValidator']['selectionLimit'] !== 1) {
            $isMultiple = 'true';
            $type = "[$type]";
        }

        $resolver = sprintf(
            '@=resolver("DomainRelationFieldValue", [value, "%s", %s])',
            $fieldDefinition->identifier,
            $isMultiple
        );

        return ['type' => $type, 'resolve' => $resolver];
    }
}