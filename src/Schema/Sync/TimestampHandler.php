<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

interface TimestampHandler
{
    public function set($timestamp);

    public function get(): int;
}
