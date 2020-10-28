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
use MongoDB\Driver\BulkWrite as MongoWrite;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Manager as MongoServer;
use MongoDB\Driver\Query as MongoQuery;
use MongoDB\Driver\WriteConcern;
use Exception;
use function array_keys;
use function extension_loaded;
use function unserialize;
use function serialize;

/**
 * A cache driver class used for MongoDB.
 */
class Mongo extends CacheProvider
{
    /**
     * The MongoDB Manager instance.
     *
     * @var \MongoDB\Driver\Manager|null
     */
    protected $mongo = null;

    /**
     * Use the default "test" dbname, if not specify any.
     *
     * @var string
     */
    protected $dbname = 'test';

    /**
     * Collection name.
     *
     * @var string
     */
    protected $collection = 'cache_data';

    /**
     * The write concern.
     *
     * @var \MongoDB\Driver\WriteConcern|null
     * @see https://www.php.net/manual/en/class.mongodb-driver-writeconcern.php
     */
    protected $concern;

    /**
     * Constructor.
     *
     * @param array $setting The settings.
     * 
     * @throws CacheException
     */
    public function __construct(array $setting = [])
    {
        $config = [
            'host'       => '127.0.0.1',
            'port'       => 27017,
            'user'       => null,
            'pass'       => null,
            'dbname'     => 'test',
            'collection' => 'cache_data',
        ];

        foreach (array_keys($config) as $key) {
            if (isset($setting[$key])) {
                $config[$key] = $setting[$key];
            }
        }

        $this->connect($config);

        $this->dbname     = $config['dbname'];
        $this->collection = $config['collection'];
    }

    /**
     * Connect to MongoDB server.
     *
     * @param array $config The settings.
     * 
     * @return void
     * 
     * @throws CacheException
     */
    protected function connect(array $config): void
    {
        if (extension_loaded('mongodb')) {
            try {
                $auth = '';
                $dababase = '';

                if (
                    !empty($config['user']) && 
                    !empty($config['pass'])
                ) {
                    $auth = $config['user'] . ':' . $config['pass'] . '@';
                }

                if (!empty($config['dbname'])) {
                    $dababase = '/' . $config['dbname'];
                }

                // Basic => mongodb://127.0.0.1:27017
                // mongodb://user:pass@127.0.0.1:27017/dbname
                $command = 'mongodb://' . $auth . $config['host'] . ':' . $config['port'] . $dababase;
    
                $this->mongo = new MongoServer($command);
                $this->concern = new WriteConcern(WriteConcern::MAJORITY, 1000);

            // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                throw new CacheException($e->getMessage());
            }
            // @codeCoverageIgnoreEnd
            return;
        }

        // @codeCoverageIgnoreStart
        throw new CacheException(
            'PHP MongoDB extension is not installed on your system.'
        );
        // @codeCoverageIgnoreEnd
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
        $filter = [
            '_id' => $this->getKeyName($key),
        ];

        $option = [];

        $query = new MongoQuery($filter, $option);
        $cursor = $this->mongo->executeQuery($this->getCollectionName(), $query);

        $data = [];
        foreach($cursor as $document) {
            $data[] = unserialize($document->content);
        }

        if (empty($data)) {
            return [];
        }

        return $data[0];
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

        $filter = [
            '_id'=> $this->getKeyName($key),
        ];

        $data = [
            'content'  => serialize($contents),
            'sc_cache' => 1,
        ];

        $option = [
            'multi'  => false, 
            'upsert' => true,
        ];

        $bulk = new MongoWrite();
        $bulk->update($filter, $data, $option);

        return $this->doWriteOperation($bulk);
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
        $bulk = new MongoWrite();

        $bulk->delete([
            '_id' => $this->getKeyName($key),
        ]);

        return $this->doWriteOperation($bulk);
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        $bulk = new MongoWrite();

        $bulk->delete([
            'sc_cache' => 1,
        ]);

        return $this->doWriteOperation($bulk);
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
        $filter = [
            '_id' => $this->getKeyName($key),
        ];

        $option = [];

        $query = new MongoQuery($filter, $option);
        $cursor = $this->mongo->executeQuery($this->getCollectionName(), $query);
        $results = $cursor->toArray();

        if (empty($results)) {
            return false;
        }

        return true;
    }

    /**
     * Perform the write operation and return the result.
     * 
     * @param object $bulk The \MongoDB\Driver\BulkWrite instance.
     *
     * @return bool
     */
    private function doWriteOperation(MongoWrite $bulk): bool
    {
        try {
            $this->mongo->executeBulkWrite(
                $this->getCollectionName(),
                $bulk,
                $this->concern
            );
        // @codeCoverageIgnoreStart
        } catch (BulkWriteException $e) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        return true;
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

    /**
     * Get the collection name.
     *
     * @return string
     */
    private function getCollectionName(): string
    {
        return $this->dbname . '.' . $this->collection; 
    }

    /**
     * Fetch all cache items.
     *
     * @return array
     */
    protected function getAll(): array
    {
        $list = [];

        $query = new MongoQuery([]);
        $cursor = $this->mongo->executeQuery($this->getCollectionName(), $query);

        foreach ($cursor as $document) {
            $key   = str_replace('sc_', '', $document->_id);
            $value = unserialize($document->content);

            $list[$key] = $value;
        }
        return $list;
    }
}
