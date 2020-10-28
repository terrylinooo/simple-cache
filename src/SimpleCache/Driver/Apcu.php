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
 * Provided by APCu extension.
 */
use APCUIterator;
use function apcu_clear_cache;
use function apcu_delete;
use function apcu_exists;
use function apcu_fetch;
use function apcu_store;

/**
 * A cache driver class provided by APCu (APC User Cache)
 *
 * @see https://www.php.net/manual/en/book.apcu.php
 */
class Apcu extends CacheProvider
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
        if (!function_exists('apcu_fetch')) {
            // @codeCoverageIgnoreStart
            throw new CacheException(
                'APCu extension is not enable.'
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
        $content = apcu_fetch($this->getKeyName($key), $success);

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

        $result = apcu_store(
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
        return apcu_delete($this->getKeyName($key));
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        return apcu_clear_cache();
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
        return apcu_exists($this->getKeyName($key));
    }

    /**
     * Fetch all cache items.
     *
     * @return array
     */
    protected function getAll(): array
    {
        $list = [];

        foreach (new APCUIterator('/^sc_/') as $item) {
            $key = str_replace('sc_', '', $item['key']);
            $value = unserialize($item['value']);
            $list[$key] = $value;
        }

        return $list;
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
        return 'sc_' . $key;
    }
}

