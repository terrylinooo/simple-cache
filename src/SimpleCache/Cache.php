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
use Shieldon\SimpleCache\Exception\CacheArgumentException;
use function file_exists;
use function is_string;
use function strtolower;
use function ucfirst;

/**
 * The base Cache Adapter class.
 */
class Cache
{
    /**
     * The cache driver.
     *
     * @var null|CacheInterface
     */
    protected $driver;

    /**
     * Constructor.
     *
     * @param string|CacheInterface $driver   The cache driver.
     * @param array                 $settings The settings.
     * 
     * @throws CacheException
     */
    public function __construct($driver = '', array $settings = [])
    {
        if ($driver instanceof CacheInterface) {
            $this->driver = $driver;

        } elseif (is_string($driver)) {
            $class = ucfirst(strtolower($driver));

            if (file_exists(__DIR__ . '/Driver/' . $class . '.php')) {
                $class = '\Shieldon\SimpleCache\Driver\\' . $class;

                $this->driver = new $class($settings);
            }
        }

        if (!$this->driver) {
            throw new CacheArgumentException(
                'The data driver is not set correctly.'
            );
        }
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function get($key, $default = null)
    {
        return $this->driver->get($key, $default);
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->driver->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function delete($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function clear()
    {
        return $this->driver->clear();
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function has($key)
    {
        return $this->driver->has($key);
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->driver->getMultiple($keys, $default);
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->driver->setMultiple($values, $ttl);
    }

    /**
     * @inheritDoc CacheInterface
     */
    public function deleteMultiple($keys)
    {
        return $this->driver->deleteMultiple($keys);
    }

    /**
     * Create or rebuid the data schema.
     * This method is avaialbe for Mysql and Sqlite drivers.
     *
     * @return bool
     */
    public function rebuild(): bool
    {
        if (method_exists($this->driver, 'rebuild')) {
            return $this->driver->rebuild();
        }

        return false;
    }
}