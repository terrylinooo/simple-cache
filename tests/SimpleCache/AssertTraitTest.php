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
use Shieldon\SimpleCache\Cache;
use Shieldon\SimpleCache\Exception\CacheArgumentException;
use Shieldon\SimpleCache\Exception\CacheException;

class AssertTraitTest extends CacheTestCase
{
    public function testAssertArgumentStringGet()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->get(['this_is_an_array']);
    }

    public function testAssertArgumentStringSet()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->set(['this_is_an_array'], 'test');
    }

    public function testAssertValidTypeOfTtlSet()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->set('foo', 'bar', 'invalid_ttl_value');
    }

    public function testAssertArgumentStringHas()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->has(['this_is_an_array']);
    }

    public function testAssertArgumentIterableSetMultiple()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->setMultiple(['this_is_an_array'], 300);
    }

    public function testAssertValidTypeOfTtlSetMultiple()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->setMultiple(['foo' => 'bar'], 'invalid_ttl_value');
    }

    public function testAssertArgumentIterableDeleteMultiple()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('file', [
            'storage' => create_tmp_directory()
        ]);

        $driver->deleteMultiple('this_is_an_array');
    }

    public function testAssertDirectoryWritable()
    {
        $this->expectException(CacheException::class);

        $driver = new Cache('file', [
            'storage' => __DIR__ . '/../directory_not_exist'
        ]);
    }

    public function testAssertSettingFields()
    {
        $this->expectException(CacheArgumentException::class);

        $driver = new Cache('mysql');
    }
}
