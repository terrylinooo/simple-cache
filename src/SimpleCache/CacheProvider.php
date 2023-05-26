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

namespace Shieldon\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use Shieldon\SimpleCache\AssertTrait;
use DateInterval;
use Datetime;
use function intval;
use function is_null;
use function rand;
use function time;

/**
 * The abstract class for cache service providers.
 */
abstract class CacheProvider implements CacheInterface
{
    use AssertTrait;

    /**
     * The type of cache driver.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Get a cache by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $this->assertArgumentString($key);

        $data = $this->doGet($key);

        if (!empty($data)) {
            if ($this->isExpired($data['ttl'], $data['timestamp'])) {
                $this->delete($key);
                $data['value'] = $default;
            }

            $default = $data['value'];
        }

        return $default;
    }

    /**
     * Set a cache by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->assertArgumentString($key);
        $this->assertValidTypeOfTtl($ttl);

        $timestamp = time();

        if (is_null($ttl)) {
            $ttl = 0;
        } elseif ($ttl instanceof DateInterval) {
            $datetimeObj = new DateTime();
            $datetimeObj->add($ttl);

            $ttl = $datetimeObj->getTimestamp() - $timestamp;
        }

        return $this->doSet($key, $value, $ttl, $timestamp);
    }

    /**
     * Delete a cache by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function delete($key)
    {
        $this->assertArgumentString($key);

        return $this->doDelete($key);
    }

    /**
     * Clear all caches by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function clear()
    {
        return $this->doClear();
    }

    /**
     * Check if a cache exists by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function has($key)
    {
        $this->assertArgumentString($key);

        if ($this->doHas($key)) {
            return true;
        }

        return false;
    }

    /**
     * Get multiple caches by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $this->assertArgumentIterable($keys);

        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->get($key, $default);
        }

        return $data;
    }

    /**
     * Set multiple caches by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $this->assertArgumentIterable($values);

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        return true;
    }

    /**
     * Delete multiple caches by an extended Cache Driver.
     *
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        $this->assertArgumentIterable($keys);

        foreach ($keys as $key) {
            if (!$this->doDelete($key)) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }
        }

        return true;
    }

    /**
     * Performing cache data garbage collection for drivers that don't have
     * ability to remove expired items automatically.
     * This method is not needed for Redis and Memcached driver.
     *
     * @param int $probability Numerator.
     * @param int $divisor     Denominator.
     *
     * @return array
     */
    public function gc(int $probability, int $divisor): array
    {
        if ($probability > $divisor) {
            $probability = $divisor;
        }

        $chance = intval($divisor / $probability);
        $hit    = rand(1, $chance);
        $list   = [];

        if ($hit === 1) {
            // Always return [] from Redis and Memcached driver.
            $data = $this->getAll();

            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $ttl      = (int) $value['ttl'];
                    $lasttime = (int) $value['timestamp'];

                    if ($this->isExpired($ttl, $lasttime)) {
                        $this->delete($key);

                        $list[] = $key;
                    }
                }
            }
        }
        return $list;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check if the TTL is expired or not.
     *
     * @param int $ttl       The time to live of a cached data.
     * @param int $timestamp The unix timesamp that want to check.
     *
     * @return bool
     */
    protected function isExpired(int $ttl, int $timestamp): bool
    {
        $now = time();

        // If $ttl equal to 0 means that never expires.
        if (empty($ttl)) {
            return false;
        } elseif ($now - $timestamp < $ttl) {
            return false;
        }

        return true;
    }

    /**
     * Fetch all cache items to prepare removing expired items.
     * This method is not needed for Redis and Memcached driver because that
     * it is used only in `gc()`.
     *
     * @return array
     */
    protected function getAll(): array
    {
        return [];
    }

    /**
     * Fetch a cache by an extended Cache Driver.
     *
     * @param string $key     The key of a cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return array The data structure looks like:
     *
     * [
     *   [
     *     'value'     => (mixed) $value
     *     'ttl'       => (int)   $ttl,
     *     'timestamp' => (int)   $timestamp,
     *   ],
     *   ...
     * ]
     */
    abstract protected function doGet(string $key): array;

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
    abstract protected function doSet(string $key, $value, int $ttl, int $timestamp): bool;

    /**
     * Delete a cache by an extended Cache Driver.
     *
     * @param string $key The key of a cache.
     *
     * @return bool
     */
    abstract protected function doDelete(string $key): bool;

    /**
     * Delete all caches by an extended Cache Driver.
     *
     * @return bool
     */
    abstract protected function doClear(): bool;

    /**
     * Check if a cahce exists or not.
     *
     * @param string $key The key of a cache.
     *
     * @return bool
     */
    abstract protected function doHas(string $key): bool;
}
