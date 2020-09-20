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
use Shieldon\SimpleCache\Driver\File;

class FileTest extends CacheTestCase
{
    public function testFileDriverCombinedTests()
    {
        $cache = new File([
            'storage' => create_tmp_directory()
        ]);

        // Test method `get()` and `get()`
        $cache->set('foo', 'bar', 300);
        $this->assertSame('bar', $cache->get('foo'));

        // Test method `has()`
        $this->assertTrue($cache->has('foo'));
        $this->assertFalse($cache->has('foo2'));

        // Test method `delete()`
        $cache->delete('foo');
        $this->assertFalse($cache->has('foo'));
    }

    public function testFileDriverClearAll()
    {
        $cache = new File([
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

    public function testFileDriverCacheExpired()
    {
        $cache = new File([
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