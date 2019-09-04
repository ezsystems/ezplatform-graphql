<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Schema\Domain\Content;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class NameHelper
{
    /**
     * @var \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter
     */
    private $caseConverter;

    public function __construct()
    {
        $this->caseConverter = new CamelCaseToSnakeCaseNameConverter(null, false);
    }

    public function domainContentCollectionField(ContentType $contentType)
    {
        return $this->pluralize(lcfirst($this->toCamelCase($contentType->identifier)));
    }

    public function domainContentName(ContentType $contentType)
    {
        return ucfirst($this->toCamelCase($contentType->identifier)) . 'Content';
    }

    public function domainContentConnection($contentType)
    {
        return ucfirst($this->toCamelCase($contentType->identifier)) . 'ContentConnection';
    }

    public function domainContentCreateInputName(ContentType $contentType)
    {
        return ucfirst($this->toCamelCase($contentType->identifier)) . 'ContentCreateInput';
    }

    public function domainContentUpdateInputName($contentType)
    {
        return ucfirst($this->toCamelCase($contentType->identifier)) . 'ContentUpdateInput';
    }

    public function domainContentTypeName(ContentType $contentType)
    {
        return ucfirst($this->toCamelCase($contentType->identifier)) . 'ContentType';
    }

    public function domainContentField(ContentType $contentType)
    {
        return lcfirst($this->toCamelCase($contentType->identifier));
    }

    public function domainMutationCreateContentField($contentType)
    {
        return 'create' . ucfirst($this->domainContentField($contentType));
    }

    public function domainMutationUpdateContentField($contentType)
    {
        return 'update' . ucfirst($this->domainContentField($contentType));
    }

    public function domainGroupName(ContentTypeGroup $contentTypeGroup)
    {
        return 'DomainGroup' . ucfirst($this->toCamelCase($this->sanitizeContentTypeGroupIdentifier($contentTypeGroup)));
    }

    public function domainGroupTypesName(ContentTypeGroup $contentTypeGroup)
    {
        return sprintf(
            'DomainGroup%sTypes',
            ucfirst($this->toCamelCase(
                $this->sanitizeContentTypeGroupIdentifier($contentTypeGroup)
            ))
        );
    }

    public function domainGroupField(ContentTypeGroup $contentTypeGroup)
    {
        return lcfirst($this->toCamelCase($this->sanitizeContentTypeGroupIdentifier($contentTypeGroup)));
    }

    public function fieldDefinitionField(FieldDefinition $fieldDefinition)
    {
        return lcfirst($this->toCamelCase($fieldDefinition->identifier));
    }

    public function filterType(ContentType $contentType)
    {
        return $this->domainContentName($contentType) . 'Filter';
    }

    private function toCamelCase($string)
    {
        return $this->caseConverter->denormalize($string);
    }

    private function pluralize($name)
    {
        if (substr($name, -1) === 'f') {
            return substr($name, 0, -1) . 'ves';
        }

        if (substr($name, -1) === 'fe') {
            return substr($name, 0, -2) . 'ves';
        }

        if (substr($name, -1) === 'y') {
            if (in_array(substr($name, -2, 1), ['a', 'e', 'i', 'o', 'u'])) {
                return $name . 's';
            } else {
                return substr($name, 0, -1) . 'ies';
            }
        }

        if (substr($name, -2) === 'is') {
            return substr($name, 0, -2) . 'es';
        }

        if (substr($name, -2) === 'us') {
            return substr($name, 0, -2) . 'i';
        }

        if (in_array(substr($name, -2), ['on', 'um'])) {
            return substr($name, 0, -2) . 'a';
        }

        if (substr($name, -2) === 'is') {
            return substr($name, 0, -2) . 'es';
        }

        if (
            preg_match('/(s|sh|ch|x|z)$/', $name) ||
            substr($name, -1) === 'o'
        ) {
            return $name . 'es';
        }

        return $name . 's';
    }

    /**
     * Removes potential spaces in content types groups names.
     * (content types groups identifiers are actually their name).
     *
     * @param ContentTypeGroup $contentTypeGroup
     *
     * @return string
     */
    protected function sanitizeContentTypeGroupIdentifier(ContentTypeGroup $contentTypeGroup): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '_', $contentTypeGroup->identifier);
    }
}
