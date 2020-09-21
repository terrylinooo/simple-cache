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
use Shieldon\SimpleCache\Exception\CacheArgumentException;
use PDO;
use Exception;
use function file_put_contents;

/**
 * A cache driver class provided by MySQL database.
 */
class Mysql extends CacheProvider
{
    use SqlTrait;

    /**
     * Constructor.
     *
     * @param array $setting The settings.
     * 
     * @throws CacheArgumentException
     */
    public function __construct($setting)
    {
        $config = [
            'host'    => '127.0.0.1',
            'port'    => 3306,
            'user'    => null,
            'pass'    => null,
            'dbname'  => null,
            'charset' => 'utf8'
        ];

        foreach (array_keys($config) as $key) {
            if (isset($setting[$key])) {
                $config[$key] = $setting[$key];
            }
        }

        $this->assertSettingFields($config);
        $this->connect($config);
    }

    /**
     * Connect to MySQL server.
     *
     * @param array $config The settings.
     * 
     * @return void
     * 
     * @throws CacheException
     */
    protected function connect(array $config): void
    {
        $host = 'mysql' . 
            ':host='   . $config['host'] . 
            ';port='   . $config['port'] .
            ';dbname=' . $config['dbname'] .
            ';charset='. $config['charset'];

        $user = $config['user'];
        $pass = $config['pass'];

        $this->db = new PDO($host, $user, $pass);
    }

    /**
     * @inheritDoc
     */
    public function rebuild(): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `cache_key` varchar(40) NOT NULL,
                `cache_value` longtext,
                PRIMARY KEY (`cache_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

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