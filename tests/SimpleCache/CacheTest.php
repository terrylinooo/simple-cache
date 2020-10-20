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
use DateInterval;

class CacheTest extends CacheTestCase
{
    public function testStart()
    {
        $this->console('Cache Provider');
    }

    public function getInstance()
    {
        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        return $driver;
    }

    public function testCacheInitialize()
    {
        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

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
        $driver = new Cache('mysql', [
            'dbname' => 'shieldon_unittest',
            'user' => 'shieldon',
            'pass' => 'taiwan',
        ]);

        $this->assertTrue($driver->rebuild());

        $driver = new Cache('sqlite', [
            'storage' => create_tmp_directory()
        ]);

        $this->assertTrue($driver->rebuild());
    }

    public function testRebuildShouldReturnFalse()
    {
        $driver = new Cache('apcu');

        $this->assertFalse($driver->rebuild());
    }
}