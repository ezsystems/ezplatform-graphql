<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\GraphQL\Schema\Domain;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Validates given name according to GraphQL specification. See http://spec.graphql.org/June2018/#sec-Names.
 */
class NameValidator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const NAME_PATTERN = '/^[_a-zA-Z][_a-zA-Z0-9]*$/';

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function isValidName(string $name): bool
    {
        return preg_match(self::NAME_PATTERN, $name) === 1;
    }

    public function generateInvalidNameWarning(string $type, string $name): void
    {
        $message = "Skipping schema generation for %s with identifier '%s' as it stands against GraphQL specification. "
            . 'For more details see http://spec.graphql.org/[latest-release]/#sec-Names.';

        $this->logger->warning(sprintf($message, $type, $name));
    }
}
