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
use Shieldon\SimpleCache\Driver\Apc;

class ApcTest extends DriverIntegrationTestCase
{
    public function getCacheDriver()
    {
        $cache = new Apc();

        return $cache;
    }

    public function testStart()
    {
        $this->console('Driver: Apc');
    }

    public function testSupport()
    {
        $this->assertTrue(apc_enabled());
    }

    public function testCacheDriver()
    {
        $driver = $this->getCacheDriver();
        $this->assertTrue($driver instanceof CacheInterface);
    }

    public function testGetAll()
    {
        $cache = $this->getCacheDriver();
        $cache->clear();
    
        $cache->setMultiple([
            'foo9' => 'bar9',
            'foo10' => 'bar10',
        ], 300);

        $reflection = new \ReflectionObject($cache);
        $method = $reflection->getMethod('getAll');
        $method->setAccessible(true);

        $items = $method->invokeArgs($cache, []);

        $this->assertEquals(count($items), 2);
        $this->assertSame($items['foo9']['value'], 'bar9');
        $this->assertSame($items['foo10']['value'], 'bar10');
    }
}