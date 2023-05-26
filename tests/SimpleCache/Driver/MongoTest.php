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

    public function testConnectWithUnixSocket()
    {
        $unixSocketFilePath = '/var/run/mongodb/mongodb.sock';

        if (file_exists($unixSocketFilePath)) {
            $cache = new Mongo([
                'unix_socket' => $unixSocketFilePath,
            ]);

            $cache->set('mongodb_socket', 'good');
            $this->assertSame('good', $cache->get('mongodb_socket'));
        } else {
            $this->console(sprintf(
                'Ingore testing with unix domain socket because that file "%s" does not exist.',
                $unixSocketFilePath
            ));
        }
    }
}
