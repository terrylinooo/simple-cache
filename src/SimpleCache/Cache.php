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
use Shieldon\SimpleCache\Driver as Driver;

/**
 * The base Cache Adapter class.
 */
class Cache
{
    /**
     * The cache driver.
     *
     * @var CacheInterface
     */
    protected $driver;

    /**
     * Constructor.
     *
     * @param CacheInterface|string $driver The cache driver.
     * 
     * @throws CacheException
     */
    public function __construct($driver = '')
    {
        if ($driver instanceof CacheInterface) {
            $this->driver = $driver;
        } elseif (is_string($driver)) {
            $driver = 'Driver' . ucfirst(strtolower($driver));
            
            // To do.
        }
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $this->driver->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->driver->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->driver->clear();
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->driver->has($key);
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->driver->getMultiple($keys, $default);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->driver->setMultiple($values, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        return $this->driver->deleteMultiple($keys);
    }
}