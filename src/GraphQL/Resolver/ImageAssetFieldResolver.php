<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\Value\ContentFieldValue;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use eZ\Publish\Core\FieldType\ImageAsset\Value as ImageAssetValue;
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

    public function resolveDomainImageAssetFieldValue($contentInfo, $fieldDefinitionIdentifier)
    {
        $contentFieldValue = $this->domainContentResolver->resolveDomainFieldValue($contentInfo, $fieldDefinitionIdentifier);

        if (!$contentFieldValue->value instanceof ImageAssetValue) {
            throw new UserError("$fieldDefinitionIdentifier is not an image asset field");
        }

        $assetValue = $this->assetMapper->getAssetValue(
            $this->contentService->loadContent($contentFieldValue->value->destinationContentId)
        );

        if (empty($assetValue->alternativeText)) {
            $assetValue->alternativeText = $contentFieldValue->value->alternativeText;
        }

        return new ContentFieldValue([
            'contentTypeId' => $contentFieldValue->contentTypeId,
            'fieldDefIdentifier' => $contentFieldValue->fieldDefIdentifier,
            'content' => $contentFieldValue->content,
            'value' => $assetValue,
        ]);
    }
}