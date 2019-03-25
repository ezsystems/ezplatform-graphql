<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\Value\ContentFieldValue;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use eZ\Publish\Core\FieldType\ImageAsset\Value as ImageAssetValue;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use Overblog\GraphQLBundle\Error\UserError;

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
        $assetValue = $this->assetMapper->getAssetValue(
            $this->contentService->loadContent($field->value->destinationContentId)
        );

        if (empty($assetValue->alternativeText)) {
            $assetValue->alternativeText = $field->value->alternativeText;
        };

        return new Field([
            'languageCode' => $field->languageCode,
            'contentTypeId' => $field->contentTypeId,
            'fieldDefIdentifier' => $field->fieldDefIdentifier,
            'fieldTypeIdentifier' => $field->fieldTypeIdentifier,
            'content' => $field->content,
            'value' => $assetValue,
        ]);
    }
}