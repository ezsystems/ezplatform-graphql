<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Value;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * A FieldValue Proxy that holds the content and field definition identifier.
 *
 * Required to be able to identify a value's FieldType to map with a GraphQL type.
 *
 * @property-read int $contentTypeId
 * @property-read int $fieldDefIdentifier
 * @property-read int $value
 */
class ContentFieldValue extends ValueObject
{
    /**
     * Identifier of the field definition this value is from.
     */
    protected $fieldDefIdentifier;

    /**
     * Id of the Content Type this value is from.
     */
    protected $contentTypeId;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected $content;

    /**
     * @var \eZ\Publish\Core\FieldType\Value
     */
    protected $value;

    public function __get($property)
    {
        if (property_exists($this->value, $property)) {
            return $this->value->$property;
        }

        return parent::__get($property);
    }

    function __toString()
    {
        return (string)$this->value;
    }
}
