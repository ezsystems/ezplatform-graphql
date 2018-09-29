<?php
/**
 * Created by PhpStorm.
 * User: bdunogier
 * Date: 23/09/2018
 * Time: 23:05
 */

namespace BD\EzPlatformGraphQLBundle\DomainContent;


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
        return lcfirst($this->toCamelCase($contentType->identifier)) . 's';
    }
    public function domainContentName(ContentType $contentType)
    {
        return ucfirst($this->toCamelCase($contentType->identifier)) . 'Content';
    }

    public function domainContentField(ContentType $contentType)
    {
        return lcfirst($this->toCamelCase($contentType->identifier));
    }

    public function domainGroupName(ContentTypeGroup $contentTypeGroup)
    {
        return 'DomainGroup' . ucfirst($this->toCamelCase($contentTypeGroup->identifier));
    }

    public function domainGroupField(ContentTypeGroup $contentTypeGroup)
    {
        return lcfirst($this->toCamelCase($contentTypeGroup->identifier));
    }

    public function fieldDefinitionField(FieldDefinition $fieldDefinition)
    {
        return lcfirst($this->toCamelCase($fieldDefinition->identifier));
    }

    private function toCamelCase($string)
    {
        return $this->caseConverter->denormalize($string);
    }
}