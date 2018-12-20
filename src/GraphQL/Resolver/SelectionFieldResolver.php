<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use EzSystems\EzPlatformGraphQL\GraphQL\Value\ContentFieldValue;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\FieldType;
use Overblog\GraphQLBundle\Error\UserError;

class SelectionFieldResolver
{
    /**
     * @var DomainContentResolver
     */
    private $domainContentResolver;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService, DomainContentResolver $domainContentResolver)
    {
        $this->contentTypeService = $contentTypeService;
        $this->domainContentResolver = $domainContentResolver;
    }

    public function resolveSelectionFieldValue(ContentInfo $contentInfo, $fieldDefinitionIdentifier)
    {
        $fieldValue = $this->domainContentResolver->resolveDomainFieldValue($contentInfo, $fieldDefinitionIdentifier);

        if (!$fieldValue->value instanceof FieldType\Selection\Value) {
            throw new UserError("$fieldDefinitionIdentifier is not an image asset field");
        }

        $fieldDefinition = $this
            ->contentTypeService->loadContentType($contentInfo->contentTypeId)
            ->getFieldDefinition($fieldDefinitionIdentifier);

        $isMultiple = $fieldDefinition->getFieldSettings()['isMultiple'];
        $options = $fieldDefinition->getFieldSettings()['options'];

        if ($isMultiple) {
            $return = [];
            foreach ($fieldValue->value->selection as $selectedItemId) {
                $return[] = $options[$selectedItemId];
            }
        } else {
            reset($fieldValue->value->selection);
            $return = $options[current($fieldValue->value->selection)];
        }

        return $return;
    }

}