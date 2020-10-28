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
use Shieldon\SimpleCache\Driver\Sqlite;

class SqliteTest extends DriverIntegrationTestCase
{
    public function getCacheDriver()
    {
        $cache = new Sqlite([
            'storage' => create_tmp_directory()
        ]);

        return $cache;
    }

    public function testStart()
    {
        $this->console('Driver: SQLite');
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