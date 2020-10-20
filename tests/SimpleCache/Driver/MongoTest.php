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
use Shieldon\SimpleCache\Driver\Mongo;
use MongoDB\Driver\Exception\AuthenticationException;

class MongoTest extends DriverIntegrationTestCase
{
    public function testStart()
    {
        $this->console('Driver: MongoDB');
    }

    public function getCacheDriver()
    {
        $cache = new Mongo();

        return $cache;
    }

    public function testCacheDriver()
    {
        $driver = $this->getCacheDriver();
        $this->assertTrue($driver instanceof CacheInterface);
    }

    public function testFailedAuthentication()
    {
        $this->expectException(AuthenticationException::class);

        $cache = new Mongo([
            'user' => 'admin',
            'pass' => '1234',
        ]);

        $cache->set('hey', 'hello', 5);
        $cache->get('hey');
    }
}