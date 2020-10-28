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
use function unserialize;
use function serialize;

/**
 * Provided by WinCache
 */
use function wincache_ucache_get;
use function wincache_ucache_set;
use function wincache_ucache_delete;
use function wincache_ucache_clear;
use function wincache_ucache_exists;

/**
 * A cache driver class provided by WinCache (Windows Cache for PHP)
 * 
 * Note: This class is excluded from the unit tests since it is only used on
 * Windows server. And all our tests are run on Linux system.
 *
 * @see https://www.php.net/manual/en/book.wincache.php
 */
class Wincache extends CacheProvider
{
    /**
     * Constructor.
     *
     * @param array $setting The settings.
     * 
     * @throws CacheException
     */
    public function __construct(array $setting = [])
    {
        if (!function_exists('wincache_ucache_get')) {
            throw new CacheException(
                'WinCache extension is not enable.'
            );
        }

        unset($setting);
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
        $success = false;
        $content = wincache_ucache_get($key, $success);

        if (empty($content) || !$success) {
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

        $result = wincache_ucache_set(
            $key,
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
        return wincache_ucache_delete($key);
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        return wincache_ucache_clear();
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
        return wincache_ucache_exists($key);
    }

    /**
     * Fetch all cache items.
     *
     * @return array
     */
    protected function getAll(): array
    {
        $list = [];
        $info = wincache_ucache_info();

        foreach ($info['ucache_entries'] as $item) {
            $key = $item['key_name'];
            $value = $this->doGet($key);
            $list[$key] = $value;
        }
        return $list;
    }
}

