<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\Exception;

use InvalidArgumentException;

class UnsupportedFieldInputFormatException extends InvalidArgumentException
{
    public function __construct($fieldType, $format)
    {
        parent::__construct("Unsupported $fieldType input format $format");
    }
}
