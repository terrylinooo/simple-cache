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
use DirectoryIterator;
use function chmod;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function rtrim;
use function serialize;
use function unlink;
use function unserialize;

/**
 * A cache driver class provided by local file system.
 */
class File extends CacheProvider
{
    /**
     * The absolute path of the storage's directory.
     * It must be writable.
     *
     * @var string
     */
    protected $storage = '/tmp/simple-cache';

    /**
     * Constructor.
     *
     * @param array $setting The settings.
     * 
     * @throws CacheException
     */
    public function __construct(array $setting)
    {
        if (isset($setting['storage'])) {
            $this->storage = rtrim($setting['storage'], '/');
        }

        echo $this->storage;

        $this->assertDirectoryWritable($this->storage);
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
        $filePath = $this->getFilePath($key);

        if (!is_file($filePath)) {
			return [];
        }

		$data = unserialize(file_get_contents($filePath));

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

        $filePath = $this->getFilePath($key);
        
        if (file_put_contents($filePath, serialize($contents))) {
            chmod($filePath, 0640);
            return true;
        }

		return false;
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
        $filePath = $this->getFilePath($key);

        return is_file($filePath) ? unlink($filePath) : false;
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        $directory = new DirectoryIterator($this->storage);

        foreach ($directory as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                unlink($file->getRealPath());
            }
        }

        return true;
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
        return file_exists($this->getFilePath($key));
    }

    /**
     * Get the path of a cache file.
     *
     * @param string $key The key of a cache.
     *
     * @return string
     */
    private function getFilePath(string $key): string
    {
        return $this->storage . '/' . $key . '.cache';
    }
}