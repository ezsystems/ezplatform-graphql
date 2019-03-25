<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler;

use eZ\Publish\SPI\FieldType\Value;

interface FieldTypeInputHandler
{
    public function toFieldValue($input, $inputFormat = null): Value;
}
