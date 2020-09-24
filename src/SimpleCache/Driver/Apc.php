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
 * Provided by APC extension.
 */
use function apc_clear_cache;
use function apc_delete;
use function apc_exists;
use function apc_fetch;
use function apc_store;

/**
 * A cache driver class provided by APC (Alternative PHP Cache).
 *
 * @see https://www.php.net/manual/en/book.apc.php
 */
class Apc extends CacheProvider
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
        if (!function_exists('apc_fetch')) {
            // @codeCoverageIgnoreStart
            throw new CacheException(
                'APC extension is not enable.'
            );
            // @codeCoverageIgnoreEnd
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
		$content = apc_fetch($key, $success);

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
            'value'     => $value
        ];

        $result = apc_store(
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
        return apc_delete($key);
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        return apc_clear_cache('user');
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
        return apc_exists($key);
    }
}

