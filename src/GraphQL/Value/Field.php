<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Value;

use eZ\Publish\API\Repository\Values as ApiValues;

/**
 * Custom Field that proxies properties to the Field Value. Otherwise similar to the one from API.
 */
class Field extends ApiValues\Content\Field
{
    public function __get($property)
    {
        if (property_exists($this->value, $property)) {
            return $this->value->$property;
        }

        return parent::__get($property);
    }

    public function __toString()
    {
        return (string)$this->value;
    }

    public static function fromField(?ApiValues\Content\Field $field)
    {
        return new self(get_object_vars($field));
    }
}
