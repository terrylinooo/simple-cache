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
use DateInterval;
use function gettype;

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
}