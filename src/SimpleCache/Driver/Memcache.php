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
use Memcache as MemcacheServer;
use Exception;
use function array_keys;

/**
 * A cache driver class provided by Memcache
 *
 * @see https://www.php.net/manual/en/book.memcache.php
 */
class Memcache extends CacheProvider
{
    protected $type = 'memcache';

    /**
     * The Memcache instance.
     *
     * @var \Memcache|null
     */
    protected $memcache = null;

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

            // If the UNIX socket is set, host and port will be ignored.
            'unix_socket' => '',
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
        if (extension_loaded('memcache')) {
            try {
                $this->memcache = new MemcacheServer();

                if (!empty($config['unix_socket'])) {
                    // @codeCoverageIgnoreStart
                    $this->memcache->addServer(
                        'unix://' . $config['unix_socket'],
                        0
                    );
                    // @codeCoverageIgnoreEnd
                } else {
                    $this->memcache->addServer(
                        $config['host'],
                        $config['port'],
                        true,
                        1
                    );
                }

            // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                throw new CacheException($e->getMessage());
            }
            // @codeCoverageIgnoreEnd
            return;
        }

        // @codeCoverageIgnoreStart
        throw new CacheException(
            'PHP Memcache extension is not installed on your system.'
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
        $content = $this->memcache->get($key);

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
            'value'     => $value,
        ];

        $result = $this->memcache->set(
            $key,
            $contents,
            0,
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
        return $this->memcache->delete($key);
    }

    /**
     * Delete all caches by an extended Cache Driver.
     *
     * @return bool
     */
    protected function doClear(): bool
    {
        return $this->memcache->flush();
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
        $content = $this->memcache->get($key);

        if (empty($content)) {
            return false;
        }
        return true;
    }
}
