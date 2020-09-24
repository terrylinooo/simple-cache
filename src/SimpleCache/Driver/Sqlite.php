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
use PDO;
use function file_put_contents;
use function rtrim;

/**
 * A cache driver class provided by SQLite database.
 */
class Sqlite extends CacheProvider
{
    use SqlTrait;

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
    public function __construct(array $setting = [])
    {
        if (isset($setting['storage'])) {
            $this->storage = rtrim($setting['storage'], '/');
        }

        $this->assertDirectoryWritable($this->storage);

        $this->db = new PDO('sqlite:' . $this->storage . '/cache.sqlite3');
    }

    /**
     * Delete all caches by an extended Cache Driver.
     * 
     * @return bool
     */
    protected function doClear(): bool
    {
        $sql = 'DELETE FROM ' . $this->table;

        $query = $this->db->prepare($sql);
        $result = $query->execute();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function rebuild(): bool
    {
        try {

            $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
                cache_key VARCHAR(40) PRIMARY KEY,
                cache_value LONGTEXT
            );";

            $this->db->query($sql);

        // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            file_put_contents('php://stderr', $e->getMessage());
            return false;
        }
        // @codeCoverageIgnoreEnd

        return true;
    }
}