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

class CacheTest extends CacheTestCase
{
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
}