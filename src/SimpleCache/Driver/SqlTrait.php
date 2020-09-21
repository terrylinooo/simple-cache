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

use PDO;
use function serialize;
use function unserialize;

/**
 * A trait implements SQL-action methods.
 */
Trait SqlTrait
{
    /**
     * The PDO instance.
     *
     * @var PDO
     */
    protected $db;
    
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'cache_data';

    /**
     * Rebuid a database table schema for SQL-like Cache driver.
     *
     * @return bool True for success, overwise.
     */
    abstract public function rebuild(): bool;

    /**
     * Fetch a cache by an extended Cache Driver.
     *
     * @param string $key The key of a cache.
     *
     * @return array
     */
    protected function doGet(string $key): array
    {
        $sql = 'SELECT * FROM ' . $this->table . '
            WHERE cache_key = :cache_key';

        $query = $this->db->prepare($sql);
        $query->bindValue(':cache_key', $key, PDO::PARAM_STR);
        $query->execute();
        $resultData = $query->fetch($this->db::FETCH_ASSOC);

        if (empty($resultData['cache_value'])) {
            return [];
        }

        $data = unserialize($resultData['cache_value']);

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
        $cacheData = $this->get($key);

        if (!empty($cacheData)) {
            $sql = 'UPDATE ' . $this->table . ' 
                SET cache_value = :cache_value 
                WHERE cache_key = :cache_key';
        } else {
            $sql = 'INSERT INTO ' . $this->table . ' (cache_key, cache_value) 
                VALUES (:cache_key, :cache_value)';
        }

        $query = $this->db->prepare($sql);

        $data = [
            'cache_key'   => $key,
            'cache_value' => serialize([
                'timestamp' => $timestamp,
                'ttl'       => $ttl,
                'value'     => $value
            ]),
        ];

        return $query->execute($data);
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
        $sql = 'DELETE FROM ' . $this->table . ' 
            WHERE cache_key = ?';

        $query = $this->db->prepare($sql);
        $result = $query->execute([$key]);

        return $result;
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        $sql = 'TRUNCATE TABLE ' . $this->table;

        $query = $this->db->prepare($sql);
        $result = $query->execute();

        return $result;
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
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . '
            WHERE cache_key = :cache_key';

        $query = $this->db->prepare($sql);
        $query->bindValue(':cache_key', $key, PDO::PARAM_STR);
        $query->execute();
        $count =$query->fetchColumn();

        return $count > 0;
    }
}