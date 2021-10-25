<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\GraphQL\Schema\Domain;

/**
 * Validates given name according to GraphQL specification. See http://spec.graphql.org/June2018/#sec-Names.
 */
class NameValidator
{
    private const NAME_PATTERN = '/^[_a-zA-Z][_a-zA-Z0-9]*$/';

    public function isValidName(string $name): bool
    {
        return preg_match(self::NAME_PATTERN, $name) === 1;
    }
}
