<?php
/*
 * This file is part of the Shieldon Simple Cache package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use Shieldon\Test\SimpleCache\DriverIntegrationTestCase;
use Shieldon\SimpleCache\Driver\Redis;

class RedisTest extends DriverIntegrationTestCase
{
    public function getCacheDriver()
    {
        $cache = new Redis();

        return $cache;
    }

    public function testCacheDriver()
    {
        $driver = $this->getCacheDriver();
        $this->assertTrue($driver instanceof CacheInterface);
    }

    public function testInvalidUsernameAndPassword()
    {
        $cache = new Redis([
            'user' => 'hello',
            'pass' => 'world',
        ]);

        // Nothing happended??
    }
}