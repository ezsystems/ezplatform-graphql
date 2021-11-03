<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformGraphQL\Schema\Sync;

use Redis;

class RedisTimestampHandler implements TimestampHandler
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var string
     */
    private $key;

    public function __construct(Redis $redis, string $key = 'graphql_schema_timestamp')
    {
        $this->redis = $redis;
        $this->key = $key;
    }

    public function set($timestamp)
    {
        $this->redis->set($this->key, $timestamp);
    }

    public function get(): int
    {
        return $this->redis->get($this->key);
    }
}
