<?php
namespace EzSystems\EzPlatformGraphQL\Exception;

use InvalidArgumentException;

class UnsupportedFieldTypeException extends InvalidArgumentException
{
    public function __construct($fieldType, $operation)
    {
        parent::__construct(
            "The $fieldType field type is not supported for $operation"
        );
    }
}