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
use Shieldon\SimpleCache\Driver\Memcache;

class MemcacheTest extends DriverIntegrationTestCase
{
    public function getCacheDriver()
    {
        $cache = new Memcache([
            'host' => '127.0.0.1',
            'port' => 11211,
        ]);

        return $cache;
    }

    public function testStart()
    {
        $this->console('Driver: Memcache');
    }

    public function testCacheDriver()
    {
        $driver = $this->getCacheDriver();
        $this->assertTrue($driver instanceof CacheInterface);
    }

    public function testConnectWithUnixSocket()
    {
        $unixSocketFilePath = '/var/run/memcached/memcached.sock';

        if (file_exists($unixSocketFilePath)) {
            $cache = new Memcache([
                'unix_socket' => $unixSocketFilePath,
            ]);

            $cache->set('memcache_socket', 'good');
            $this->assertSame('good', $cache->get('memcache_socket'));
        } else {
            $this->console(sprintf(
                'Ingore testing with unix domain socket because that file "%s" does not exist.',
                $unixSocketFilePath
            ));
        }
    }
}
