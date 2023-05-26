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
use Redis as RedisServer;
use Exception;
use function array_keys;
use function extension_loaded;
use function unserialize;
use function serialize;
use function is_bool;

/**
 * A cache driver class provided by Redis database.
 */
class Redis extends CacheProvider
{
    protected $type = 'redis';

    /**
     * The Redis instance.
     *
     * @var \Redis|null
     */
    protected $redis = null;

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
            'port' => 6379,
            'user' => null,
            'pass' => null,

            // If the UNIX socket is set, host, port, user and pass will be ignored.
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
     * Connect to Redis server.
     *
     * @param array $config The settings.
     *
     * @return void
     *
     * @throws CacheException
     */
    protected function connect(array $config): void
    {
        if (extension_loaded('redis')) {
            try {
                $this->redis = new RedisServer();

                if (!empty($config['unix_socket'])) {
                    // @codeCoverageIgnoreStart
                    $this->redis->connect($config['unix_socket']);
                    // @codeCoverageIgnoreEnd
                } else {
                    $this->redis->connect($config['host'], $config['port']);
                    $this->auth($config);
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
            'PHP Redis extension is not installed on your system.'
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Redis authentication.
     *
     * @param array $config The user / pass data.
     * @return void
     * @codeCoverageIgnore
     */
    protected function auth(array $config = []): void
    {
        if ($this->getVersion() >= 6) {
            if (!empty($config['user']) && !empty($config['pass'])) {
                $this->redis->auth([
                    $config['user'],
                    $config['pass'],
                ]);
            }
            return;
        }

        if (!empty($config['pass'])) {
            $this->redis->auth($config['pass']);
        }
    }

    /**
     * Get Redis version number.
     */
    protected function getVersion(): int
    {
        $info = $this->redis->info();

        return (int) $info['redis_version'];
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
        $content = $this->redis->get($this->getKeyName($key));

        if (empty($content)) {
            return [];
        }
        $data = unserialize($content);

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

        if (empty($ttl)) {
            $ttl = null;
        }

        $result = $this->redis->set(
            $this->getKeyName($key),
            serialize($contents),
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
        return $this->redis->del($this->getKeyName($key)) >= 0;
    }

    /**
     * Delete all caches by an extended Cache Driver.
     *
     * @return bool
     */
    protected function doClear(): bool
    {
        $keys = $this->redis->keys('sc:*');

        if (!empty($keys)) {
            foreach ($keys as $key) {
                $this->redis->del($key);
            }
        }

        return true;
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
        $exist = $this->redis->exists($this->getKeyName($key));

        // This function took a single argument and returned TRUE or FALSE in phpredis versions < 4.0.0.

        // @codeCoverageIgnoreStart
        if (is_bool($exist)) {
            return $exist;
        }

        return $exist > 0;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the key name of a cache.
     *
     * @param string $key The key of a cache.
     *
     * @return string
     */
    private function getKeyName(string $key): string
    {
        return 'sc:' . $key;
    }
}
