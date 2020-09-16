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

/**
 * The abstract class for service providers.
 */
abstract class CacheProvider implements CacheInterface
{
    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {

    }

    /**
     * @inheritDoc
     */
    public function clear()
    {

    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {

    }
}