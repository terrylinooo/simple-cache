<?php
/*
 * This file is part of the Shieldon Simple Cache package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\SimpleCache\Driver;

use Shieldon\SimpleCache\CacheProvider;
use Shieldon\SimpleCache\Exception\CacheException;
use Memcached as MemcachedServer;
use Exception;
use function array_keys;

/**
 * A cache driver class provided by libMemcached
 * 
 * @see https://www.php.net/manual/en/book.memcached.php
 */
class Memcached extends CacheProvider
{
    /**
     * The Memcached instance.
     *
     * @var \Memcached|null
     */
    protected $memcached = null;

    /**
     * Constructor.
     *
     * @param array $setting The settings.
     * 
     * @throws CacheException
     */
    public function __construct(array $setting = [])
    {
        $config = [
            'host' => '127.0.0.1',
            'port' => 11211,
        ];

        foreach (array_keys($config) as $key) {
            if (isset($setting[$key])) {
                $config[$key] = $setting[$key];
            }
        }

        $this->connect($config);
    }

    /**
     * Connect to Memchaced server.
     *
     * @param array $config The settings.
     * 
     * @return void
     * 
     * @throws CacheException
     */
    protected function connect(array $config): void
    {
        if (extension_loaded('memcached')) {
            try {
                $this->memcached = new MemcachedServer();
                $this->memcached->addServer(
                    $config['host'],
                    $config['port'],
                    1
                );
            // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                throw new CacheException($e->getMessage());
            }
            // @codeCoverageIgnoreEnd
            return;
        }

        // @codeCoverageIgnoreStart
        throw new CacheException(
            'PHP Memcached extension is not installed on your system.'
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Fetch a cache by an extended Cache Driver.
     *
     * @param string $key The key of a cache.
     *
     * @return array
     */
    protected function doGet(string $key): array
    {
        $content = $this->memcached->get($key);

        if (empty($content)) {
            return [];
        }
        $data = $content;

        return $data;
    }

    /**
     * Set a cache by an extended Cache Driver.
     *
     * @param string $key       The key of a cache.
     * @param mixed  $value     The value of a cache. (serialized)
     * @param int    $ttl       The time to live for a cache.
     * @param int    $timestamp The time to store a cache.
     *
     * @return bool
     */
    protected function doSet(string $key, $value, int $ttl, int $timestamp): bool
    {
        $contents = [
            'timestamp' => $timestamp,
            'ttl'       => $ttl,
            'value'     => $value
        ];

        $result = $this->memcached->set(
            $key,
            $contents,
            $ttl
        );

        return $result;
    }

    /**
     * Delete a cache by an extended Cache Driver.
     *
     * @param string $key The key of a cache.
     * 
     * @return bool
     */
    protected function doDelete(string $key): bool
    {
        return $this->memcached->delete($key);
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        return $this->memcached->flush();
    }

    /**
     * Check if the cache exists or not.
     *
     * @param string $key The key of a cache.
     *
     * @return bool
     */
    protected function doHas(string $key): bool
    {
        $content = $this->memcached->get($key);

        if (empty($content)) {
            return false;
        }
        return true;
    }
}

