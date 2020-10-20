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
}