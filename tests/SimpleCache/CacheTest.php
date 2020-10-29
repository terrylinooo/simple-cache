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
use Shieldon\SimpleCache\Cache;
use Shieldon\Test\SimpleCache\CacheTestCase;
use Shieldon\SimpleCache\Driver\Mock;
use Shieldon\SimpleCache\Exception\CacheArgumentException;

class CacheTest extends CacheTestCase
{
    public function testStart()
    {
        $this->console('Cache Provider');
    }

    /**
     * Test provider.
     *
     * @param string $type     The driver's type.
     * @param array  $settings The driver's settings.
     *
     * @return Cache
     */
    public function getInstance($type = 'file', $settings = [])
    {
        switch ($type) {
            case 'apc':
            case 'apcu':
            case 'memcache':
            case 'memcached':
            case 'mongo':
            case 'redis':
                $driver = new Cache($type, $settings);
                break;

            case 'mysql':
                $settings['dbname'] = 'shieldon_unittest';
                $settings['user']   = 'shieldon';
                $settings['pass']   = 'taiwan';

                $driver = new Cache($type, $settings);
                break;

            case 'sqlite':
                $settings['storage'] = create_tmp_directory();
                $driver = new Cache($type, $settings);
                break;

            case 'file':
            default:
                $settings['storage'] = create_tmp_directory();
                $driver = new Cache('file', $settings);
                break;
        }

        return $driver;
    }

    /**
     * Test provider.
     *
     * @param string $driverType The driver's type.
     * @param bool   $hit        The GC is it or not.
     *
     * @return void
     */
    public function garbageCollection(string $driverType, bool $hit = true)
    {
        $text = 'not hit';

        if ($hit) {
            $text = 'hit';
        }

        $this->console('Garbage collection test: ' . ucfirst($driverType) . ' (' . $text . ')');

        $driver = $this->getInstance($driverType);
        $driver->clear();
        $driver->set('foo', 'aa', 3);
        $driver->set('foo2', 'bb', 3);

        $this->assertSame('aa', $driver->get('foo'));
        $this->assertSame('bb', $driver->get('foo2'));

        sleep(5);

        // Start the garbage collection.
        if ($hit) {

            $settings = [
                'gc_enable'      => true,
                'gc_divisor'     => 1,
                'gc_probability' => 2,
            ];
    
            $driver = $this->getInstance($driverType, $settings);
    
            $this->assertSame(null, $driver->get('foo'));
            $this->assertSame(null, $driver->get('foo2'));
        } else {
            $settings = [
                'gc_enable'      => true,
                'gc_divisor'     => 99999999,
                'gc_probability' => 1,
            ];
    
            $driver = $this->getInstance($driverType, $settings);
    
            sleep(3);
    
            $this->assertSame(null, $driver->get('foo'));
            $this->assertSame(null, $driver->get('foo2'));
        }
    }

    public function testCacheInitialize()
    {
        $driver = $this->getInstance();

        $reflection = new \ReflectionObject($driver);
        $t = $reflection->getProperty('driver');
        $t->setAccessible(true);
        $propertyDriver = $t->getValue($driver);

        $this->assertTrue($propertyDriver instanceof CacheInterface);
    }

    public function testDriverCombinedTests()
    {
        // Test method `set()` and `get()`
        $this->getInstance()->set('foo', 'bar', 300);
        $this->assertSame('bar', $this->getInstance()->get('foo'));

        $this->getInstance()->set('foo', 'bar bar', 300);
        $this->assertSame('bar bar', $this->getInstance()->get('foo'));

        // Test method `has()`
        $this->assertTrue($this->getInstance()->has('foo'));
        $this->assertFalse($this->getInstance()->has('foo2'));

        // Test method `delete()`
        $this->getInstance()->delete('foo');
        $this->assertFalse($this->getInstance()->has('foo'));

        // test method `setMultiple`
        $this->getInstance()->setMultiple([
            'foo3' => 'bar3',
            'foo4' => 'bar4',
        ], 300);

        $this->assertSame('bar3', $this->getInstance()->get('foo3'));
        $this->assertSame('bar4', $this->getInstance()->get('foo4'));

        // test method `getMultiple`
        $result = $this->getInstance()->getMultiple(['foo3', 'foo4', 'foo5'], 'hello');

        $this->assertEquals([
            'foo3' => 'bar3',
            'foo4' => 'bar4',
            'foo5' => 'hello',
        ], $result);

        // test method `deleteMultiple`
        $this->getInstance()->deleteMultiple(['foo3']);

        $result = $this->getInstance()->getMultiple(['foo3', 'foo4', 'foo5'], 'hello');

        $this->assertEquals([
            'foo3' => 'hello',
            'foo4' => 'bar4',
            'foo5' => 'hello',
        ], $result);

        $this->getInstance()->clear();
    }

    public function testPsr16Injection()
    {
        $psr16Driver = new Mock();
        $driver = new Cache($psr16Driver);

        $reflection = new \ReflectionObject($driver);
        $t = $reflection->getProperty('driver');
        $t->setAccessible(true);
        $propertyDriver = $t->getValue($driver);
        
        $this->assertTrue($propertyDriver instanceof CacheInterface);
    }

    public function testNotSupportedDriver()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('driver_not_exist');
    }

    public function testIsExpired()
    {
        $cache = new Mock();

        $time = time();

        $reflection = new \ReflectionObject($cache);
        $method = $reflection->getMethod('isExpired');
        $method->setAccessible(true);

        // Test zero
        $result = $method->invokeArgs($cache, [0, $time]);
        $this->assertFalse($result);
    }

    public function testRebuildShouldReturnTrue()
    {
        $driver = $this->getInstance('mysql');

        $this->assertTrue($driver->rebuild());

        $driver = new Cache('sqlite', [
            'storage' => create_tmp_directory()
        ]);

        $this->assertTrue($driver->rebuild());
    }

    public function testRebuildShouldReturnFalse()
    {
        $driver = $this->getInstance('apcu');

        $this->assertFalse($driver->rebuild());
    }

    public function testGarbageCollectionForFileDriver()
    {
        $this->garbageCollection('file');
        $this->garbageCollection('file', false);
    }

    public function testGarbageCollectionForRedisDriver()
    {
        $this->garbageCollection('redis');
    }

    public function testGarbageCollectionForMemcacheDriver()
    {
        $this->garbageCollection('memcache');
    }

    public function testGarbageCollectionForMemcachedDriver()
    {
        $this->garbageCollection('memcached');
    }

    public function testGarbageCollectionForApcDriver()
    {
        $this->garbageCollection('apc');
    }

    public function testGarbageCollectionForApcuDriver()
    {
        $this->garbageCollection('apcu');
    }

    public function testGarbageCollectionForMysqlDriver()
    {
        $this->garbageCollection('mysql');
    }

    public function testGarbageCollectionForSqliteDriver()
    {
        $this->garbageCollection('sqlite');
    }
    public function testGarbageCollectionForMongodbDriver()
    {
        $this->garbageCollection('mongo');
    }

    public function testClearExpiredItems()
    {
        $this->console('Clear expired items.');

        $driver = $this->getInstance();
        $driver->clear();
        $driver->set('foo', 'aa', 1);
        $driver->set('foo2', 'bb', 1);

        $this->assertSame('aa', $driver->get('foo'));
        $this->assertSame('bb', $driver->get('foo2'));

        sleep(2);

        $removedList = $driver->clearExpiredItems();

        $this->assertSame(null, $driver->get('foo'));
        $this->assertSame(null, $driver->get('foo2'));

        $this->assertContains('foo', $removedList);
        $this->assertContains('foo2', $removedList);
    }
}