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

    public function testStart()
    {
        $this->console('Driver: Redis');
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

    public function testConnectWithUnixSocket()
    {
        $unixSocketFilePath = '/var/run/redis/redis.sock';

        if (file_exists($unixSocketFilePath)) {
            $cache = new Redis([
                'unix_socket' => $unixSocketFilePath,
            ]);

            $cache->set('redis_socket', 'good');
            $this->assertSame('good', $cache->get('redis_socket'));
        } else {
            $this->console(sprintf(
                'Ingore testing with unix domain socket because that file "%s" does not exist.',
                $unixSocketFilePath
            ));
        }
    }
}
