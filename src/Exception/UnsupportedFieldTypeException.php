<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
