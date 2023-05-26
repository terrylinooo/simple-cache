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
use Shieldon\SimpleCache\Driver\Mock;
use Shieldon\Test\SimpleCache\DriverIntegrationTestCase;

class MockTest extends DriverIntegrationTestCase
{
    public function getCacheDriver()
    {
        $cache = new Mock();

        return $cache;
    }

    public function testStart()
    {
        $this->console('Driver: Mock');
    }

    public function testCacheDriver()
    {
        $driver = $this->getCacheDriver();
        $this->assertTrue($driver instanceof CacheInterface);
    }
}
