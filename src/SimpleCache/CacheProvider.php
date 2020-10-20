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
use function is_integer;
use function is_null;
use function time;

/**
 * The abstract class for cache service providers.
 */
abstract class CacheProvider implements CacheInterface
{
    use AssertTrait;

    /**
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
     * @inheritDoc
     */
    public function delete($key)
    {
        $this->assertArgumentString($key);

        return $this->doDelete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->doClear();
    }

    /**
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
     *
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
