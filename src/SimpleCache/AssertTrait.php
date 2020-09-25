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

use Shieldon\SimpleCache\Exception\CacheArgumentException;
use Shieldon\SimpleCache\Exception\CacheException;
use DateInterval;
use function gettype;
use function is_dir;
use function is_iterable;
use function is_null;
use function is_string;
use function is_writable;
use function sprintf;

/**
 * This assert trait provides methods to check conditions.
 */
Trait AssertTrait
{
    /**
     * Check if the variable is string or not.
     *
     * @param string $var The variable will be checked.
     *
     * @return void
     * 
     * @throws CacheArgumentException
     */
    protected function assertArgumentString($value): void
    {
        if (!is_string($value)) {
            throw new CacheArgumentException(
                sprintf(
                    'The type of value must be string, but "%s" provided.',
                    gettype($value)
                )
            );
        }
    }

    /**
     * Check if the variable is iterable or not.
     *
     * @param iterable $value The variable will be checked.
     *
     * @return void
     * 
     * @throws CacheArgumentException
     */
    protected function assertArgumentIterable($value): void
    {
        if (!is_iterable($value)) {
            throw new CacheArgumentException(
                sprintf(
                    'The type of value must be iterable, but "%s" provided.',
                    gettype($value)
                )
            );
        }
    }

    /**
     * Check if the TTL is valid type or not.
     *
     * @param int|null|DateInterval $ttl The time to live of a cached data.
     *
     * @return void
     * 
     * @throws CacheArgumentException
     */
    protected function assertValidTypeOfTtl($ttl): void
    {
        if (
            !is_null($ttl) &&
            !is_integer($ttl) &&
            !($ttl instanceof DateInterval)
        ) {
            throw new CacheArgumentException(
                sprintf(
                    'The TTL only accetps int, null and DateInterval instance, but "%s" provided.',
                    gettype($ttl)
                )
            );
        }
    }

    /**
     * Check if a directory exists and is writable.
     *
     * @param string $directory The path of a directory.
     *
     * @return void
     * 
     * @throws CacheException
     */
    protected function assertDirectoryWritable(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new CacheException(
                'The directory of the storage does not exist.'
            );
        }

        // @codeCoverageIgnoreStart

        if (!is_writable($directory)) {
            throw new CacheException(
                'The directory of the storage must be wriable'
            );
        }

        // @codeCoverageIgnoreEnd
    }

    /**
     * Check if a setting field is empty or not.
     *
     * @param array $settings The array of the settings.
     *
     * @return void
     * 
     * @throws CacheArgumentException
     */
    protected function assertSettingFields($settings): void
    {
        foreach ($settings as $k => $v) {
            if (empty($v)) {
                throw new CacheArgumentException(
                    sprintf(
                        'The setting field "%s" cannot be empty or null',
                        $k
                    )
                );
            }
        }
    }
}