<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;

class ImageAssetFieldResolver
{
    /**
     * @var DomainContentResolver
     */
    private $domainContentResolver;
    /**
     * @var ContentLoader
     */
    private $contentLoader;
    /**
     * @var AssetMapper
     */
    private $assetMapper;

    public function __construct(ContentLoader $contentLoader, DomainContentResolver $domainContentResolver, AssetMapper $assetMapper)
    {
        $this->domainContentResolver = $domainContentResolver;
        $this->contentLoader = $contentLoader;
        $this->assetMapper = $assetMapper;
    }

    public function resolveDomainImageAssetFieldValue(Field $field)
    {
        $assetField = $this->assetMapper->getAssetField(
            $this->contentLoader->findSingle(new Criterion\ContentId($field->value->destinationContentId))
        );

        if (empty($assetField->value->alternativeText)) {
            $assetField->value->alternativeText = $field->value->alternativeText;
        };

        return Field::fromField($assetField);
    }
}