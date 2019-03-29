<?php
namespace EzSystems\EzPlatformGraphQL\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;

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

    public function resolveSelectionFieldValue(Field $field, ContentInfo $contentInfo)
    {
        $fieldDefinition = $this
            ->contentTypeService->loadContentType($contentInfo->contentTypeId)
            ->getFieldDefinition($field->fieldDefIdentifier);

        $options = $this->getOptions($field, $fieldDefinition, $contentInfo);

        if ($fieldDefinition->getFieldSettings()['isMultiple']) {
            $return = [];
            foreach ($field->value->selection as $selectedItemId) {
                $return[] = $options[$selectedItemId];
            }
        } else {
            reset($field->value->selection);
            $return = $options[current($field->value->selection)];
        }

        return $return;
    }

    /**
     * Returns the options set based on the language.
     *
     * @param Field $field
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return array
     */
    private function getOptions(Field $field, FieldDefinition $fieldDefinition, ContentInfo $contentInfo)
    {
        $fieldSettings = $fieldDefinition->getFieldSettings();

        if (isset($fieldSettings['multilingualOptions'])) {
            $multilingualOptions = $fieldSettings['multilingualOptions'];
            $fieldLanguageCode = $field->languageCode;
            $mainLanguageCode = $contentInfo->mainLanguageCode;

            if (isset($multilingualOptions[$fieldLanguageCode])) {
                return $multilingualOptions[$fieldLanguageCode];
            } elseif (isset($multilingualOptions[$mainLanguageCode])) {
                return $multilingualOptions[$mainLanguageCode];
            }
        }

        return $fieldSettings['options'];
    }
}