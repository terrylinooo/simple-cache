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

use Shieldon\Test\SimpleCache\CacheTestCase;
use Shieldon\SimpleCache\Driver\Sqlite;

class SqliteTest extends CacheTestCase
{
    public function testDriverCombinedTests()
    {
        $cache = new Sqlite([
            'storage' => create_tmp_directory()
        ]);

        $cache->rebuild();

        // Test method `get()` and `get()`
        $cache->set('foo', 'bar', 300);
        $this->assertSame('bar', $cache->get('foo'));

        // Test method `has()`
        $this->assertTrue($cache->has('foo'));
        $this->assertFalse($cache->has('foo2'));

        // Test method `delete()`
        $cache->delete('foo');
        $this->assertFalse($cache->has('foo'));

        // test method `setMultiple`
        $cache->setMultiple([
            'foo3' => 'bar3',
            'foo4' => 'bar4',
        ], 300);

        $this->assertSame('bar3', $cache->get('foo3'));
        $this->assertSame('bar4', $cache->get('foo4'));

        // test method `getMultiple`
        $result = $cache->getMultiple(['foo3', 'foo4', 'foo5'], 'hello');

        $this->assertEquals([
            'foo3' => 'bar3',
            'foo4' => 'bar4',
            'foo5' => 'hello',
        ], $result);

        // test method `deleteMultiple`
        $cache->deleteMultiple(['foo3']);

        $result = $cache->getMultiple(['foo3', 'foo4', 'foo5'], 'hello');

        $this->assertEquals([
            'foo3' => 'hello',
            'foo4' => 'bar4',
            'foo5' => 'hello',
        ], $result);
    }

    public function testDriverClearAll()
    {
        $cache = new Sqlite([
            'storage' => create_tmp_directory()
        ]);

        $cache->set('foo', 'bar', 300);
        $cache->set('foo2', 'bar2', 300);
        $this->assertSame('bar', $cache->get('foo'));
        $this->assertSame('bar2', $cache->get('foo2'));

        // Clear all caches.
        $cache->clear();

        $this->assertSame(null, $cache->get('foo'));
        $this->assertSame(null, $cache->get('foo2'));
    }

    public function testDriverCacheExpired()
    {
        $cache = new Sqlite([
            'storage' => create_tmp_directory()
        ]);

        $cache->set('foo', 'bar', 5);
        $this->assertSame('bar', $cache->get('foo'));
        $this->assertTrue($cache->has('foo'));

        sleep(6);
        $this->assertSame(null, $cache->get('foo'));
        $this->assertFalse($cache->has('foo'));
    }
}