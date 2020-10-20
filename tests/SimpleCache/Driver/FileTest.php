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
use Shieldon\SimpleCache\Driver\File;
use Shieldon\Test\SimpleCache\DriverIntegrationTestCase;

class FileTest extends DriverIntegrationTestCase
{
    public function getCacheDriver()
    {
        $cache = new File([
            'storage' => create_tmp_directory()
        ]);

        return $cache;
    }

    public function testStart()
    {
        $this->console('Driver: File');
    }

    public function testCacheDriver()
    {
        $driver = $this->getCacheDriver();
        $this->assertTrue($driver instanceof CacheInterface);
    }
}