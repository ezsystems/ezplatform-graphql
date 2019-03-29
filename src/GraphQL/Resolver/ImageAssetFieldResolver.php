<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;

class ImageAssetFieldResolver
{
    /**
     * @var DomainContentResolver
     */
    private $domainContentResolver;
    /**
     * @var ContentService
     */
    private $contentService;
    /**
     * @var AssetMapper
     */
    private $assetMapper;

    public function __construct(ContentService $contentService, DomainContentResolver $domainContentResolver, AssetMapper $assetMapper)
    {
        $this->domainContentResolver = $domainContentResolver;
        $this->contentService = $contentService;
        $this->assetMapper = $assetMapper;
    }

    public function resolveDomainImageAssetFieldValue(Field $field)
    {
        $assetField = $this->assetMapper->getAssetField(
            $this->contentService->loadContent($field->value->destinationContentId)
        );

        if (empty($assetField->value->alternativeText)) {
            $assetField->value->alternativeText = $field->value->alternativeText;
        };

        return Field::fromField($assetField);
    }
}