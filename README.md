# PSR-16 Simple Cache

| GitHub Action | Travis CI |  Scrutinizer CI | Code Coverage | Code Quality |
| ------------- | --------- | --------------- | ------------- | ------------ |
| ![build](https://github.com/terrylinooo/simple-cache/workflows/build/badge.svg?branch=master) | [![Build Status](https://travis-ci.org/terrylinooo/simple-cache.svg?branch=master)](https://travis-ci.org/terrylinooo/simple-cache) | [![Build Status](https://scrutinizer-ci.com/g/terrylinooo/simple-cache/badges/build.png?b=master)](https://scrutinizer-ci.com/g/terrylinooo/simple-cache/build-status/master) |  [![codecov](https://scrutinizer-ci.com/g/terrylinooo/simple-cache/badges/coverage.png?b=master)](https://codecov.io/gh/terrylinooo/simple-cache) |  [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/terrylinooo/simple-cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/terrylinooo/simple-cache/?branch=master)

Caching, a common performance-boosting technique, is a staple feature in many frameworks and libraries. This interoperability allows libraries to utilize existing caching implementations rather than creating their own. PSR-6 has addressed this issue, but its formal and verbose nature complicates simple use cases. PSR-16 is a simpler approach seeks to create a streamlined standard interface for common situations, ensuring compatibility with PSR-6 in a straightforward manner.

***Showcase***

Simple Cache is utilized in [Cache Master](https://github.com/terrylinooo/cache-master), a [WordPress Cache Plugin](https://wordpress.org/plugins/cache-master/), and it performs excellently. Check it out if you're running WordPress sites; it won't let you down.

#### Built-in drivers:

The required parameters are marked by an asterisk (*)

| Driver name  | `($driver)` | PHP modules | `($config)`                                                           |
| ------------ | ----------- | ----------- | --------------------------------------------------------------------- |
| File         | `file`      | -           | `*storage`                                                            |
| Redis        | `redis`     | redis       | `host`, `port`, `user`, `pass`, `unix_socket`                         |
| MongoDB      | `mongo`     | mongodb     | `host`, `port`, `user`, `pass`, `dbname`, `collection`, `unix_socket` |
| MySQL        | `mysql`     | pdo_mysql   | `host`, `port`, `*user`, `*pass`, `*dbname`, `table`, `charset`       |
| SQLite       | `sqlite`    | pdo_sqlite  | `*storage`                                                            |
| APC          | `apc`       | apc         | -                                                                     |
| APCu         | `apcu`      | apcu        | -                                                                     |
| Memcache     | `memcache`  | memcache    | `host`, `port`, `unix_socket`                                         |
| LibMemcached | `memcached` | memcached   | `host`, `port`, `unix_socket`                                         |
| WinCache     | `wincache`  | wincache    | -                                                                     |

Note: 

- **WinCache** is excluded from unit testing since it's only used on Windows, and the testing processes are done on Linux environment.
- `unix_socket` is empty by default, accepting the absolute path of the unix domain socket file. If it is set, `host` and `port` will be ignored.

The following command displays a list of installed PHP modules.

```bash
php -m
```
Before using, make sure the required PHP modules are installed on your system.

---

## Table of Contents

- Install
- Usage
    - Cache
        - Built-in drivers
        - __construct
        - $driver
        - $config
- API
    - set
    - get
    - has
    - delete
    - getMultiple
    - setMultiple
    - deleteMultiple
    - clear
    - clearExpiredItems `(Non-PSR-16)`
- Build Data Schema
    - MySQL
    - SQLite
- Garbage Collection
- Author
- License

---

## Install

```bash
composer require shieldon/simple-cache
```

## Usage

### `Cache`

Class `Cache` is an adapter that not only allows the implemented instance of `Psr\SimpleCache\CacheInterface`, but also has built-in drivers already.

####  __construct(`$driver = ''`, `$config = []`)

Create a cache handler using the file driver.

Example:

```php
$driver = new \Shieldon\SimpleCache\Cache('file', [
    'storage' => __DIR__ . '/../tmp'
]);
```

#### `$driver`

(string|CacheInterface)

The class name of a built-in driver, or a PSR-16 driver that implements `Psr\SimpleCache\CacheInterface`.

#### `$config`

(array)

An array of parameters will be passed to a built-in driver.

Example:

*Redis*
```php
$config = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'user' => null,
    'pass' => null,
];
```

*File*
```php
$config = [
    'storage' => '/tmp/simple-cache',
];
```

*Mysql*
```php
$config = [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'user'    => null,
    'pass'    => null,
    'dbname'  => null,
    'table'   => 'cache_data',
    'charset' => 'utf8'
];
```

*Sqlite*
```php
$config = [
    'storage' => '/tmp/simple-cache',
];
```

*Mongo*
```php
$config = [
    'host'       => '127.0.0.1',
    'port'       => 27017,
    'user'       => null,
    'pass'       => null,
    'database'   => 'test',
    'collection' => 'cache_data',
];
```

*Memcache*, *Memcached*
```php
$config = [
    'host' => '127.0.0.1',
    'port' => 11211,
];
```

---

## API

Those API methods are defined on `Psr\SimpleCache\CacheInterface`. Please check out the [PSR-16 document](https://www.php-fig.org/psr/psr-16/) to get the detailed explanation.

- set
- get
- has
- delete
- setMultiple
- getMultiple
- deleteMultiple
- clear
- clearExpiredItems *(Non-PSR-16)*

### set

```php
public function set(string $key, mixed value, $ttl = null);
```

Note that **$ttl** accepts `null`,`int`,`DateInterval`.
The `null` means that the key never expires until deleted.

Example:

```php
$cache->set('foo', 'bar', 300);
$cache->set('foo2', 'bar2');

$array = [
    'hello' => 'world',
    'yes' => 'Taiwan',
];

$cache->set('foo3', $array);
$cache->set('foo4', $array, 300);
```

### get

```php
public function get(string $key, mixed $default = null): mixed
```

Example:

```php
echo $cache->get('foo', 'placeholder');
// bar

sleep(301);

echo $cache->get('foo', 'placeholder');
// placeholder

echo $cache->get('foo');
// null

echo $cache->get('foo2', 'placeholder');
// bar2

$example = $cache->get('foo3', 'placeholder');
var_dump($example);
// string(11) "placeholder"

$example = $cache->get('foo4', 'placeholder');
var_dump($example);
/* 
    array(2) {
    ["hello"]=>
    string(5) "world"
    ["yes"]=>
    string(6) "Taiwan"
    }
*/
```

### has

```php
public function has(string $key): bool
```

Example:

```php
if ($cache->has('foo3')) {
    echo 'foo3 exists.';
} else {
    echo 'foo3 does not exist.';
}
// foo3 exists.
```

### delete

```php
public function delete(string $key): bool
```

Example:

```php

if ($cache->delete('foo3')) {
    echo 'foo3 has been deleted successfully.';
} else {
    echo 'Failed to delete key foo3.';
}
// foo3 has been deleted successfully.

if ($cache->has('foo3')) {
    echo 'foo3 exists.';
} else {
    echo 'foo3 does not exist.';
}
// foo3 does not exist.
```

### setMultiple

```php
public function setMultiple(iterable $values, $ttl = null): bool
```

Note that **$ttl** accepts `null`,`int`,`DateInterval`.
The `null` means that the key never expires until deleted.

Example:

```php
$array = [
    'bar' => 'foo',
    'bar2' => 'foo2',
];

$cache->setMultiple($array, 300);
```

### getMultiple

```php
public function getMultiple(array $keys, mixed $default = null): iterable
```

Example:

```php
$example = $cache->getMultiple(['bar', 'bar2', 'bar3'], 'hello');
var_dump($example);
/* 
    array(3) {
    ["bar"]=>
    string(3) "foo"
    ["bar2"]=>
    string(4) "foo2"
    ["bar3"]=>
    string(5) "hello"
    }
*/
```

### deleteMultiple

```php
public function deleteMultiple(array $keys): bool
```

Example:

```php
if ($cache->deleteMultiple(['bar', 'bar2'])) {
    echo 'bar and bar2 have been deleted successfully.';
} else {
    echo 'Failed to delete keys bar or bar2.';
}
// bar and bar2 have been deleted successfully.

$example = $cache->getMultiple(['bar', 'bar2', 'bar3'], 'hello');
var_dump($example);
/* 
    array(3) {
    ["bar"]=>
    string(5) "hello"
    ["bar2"]=>
    string(5) "hello"
    ["bar3"]=>
    string(5) "hello"
    }
*/
```

### clear

```php
public function clear(): bool
```

Example:

```php
if ($cache->clear()) {
    echo 'All cached data has been deleted successfully.';
} else {
    echo 'Failed to delete the cached data.';
}
// All cached data has been deleted successfully.
```

### clearExpiredItems `Non-PSR-16`

```php
public function clearExpiredItems(): array
```

This method returns a list of deleted cache keys.

*Note*: **Redis** and **Memcache**, **Memcached** drivers will always return an empty array. See *Garbage Collection* section below.

Example:

```php
$cache->set('foo', 'bar', 300);
$cache->set('foo2', 'bar2', 5);
$cache->set('foo3', 'bar3', 5);

sleep(6);

$expiredItems = $cache->clearExpiredItems();
var_dump($expiredItems);

/* 
    array(2) {
    [0]=>
    string(4) "foo2"
    [1]=>
    string(4) "foo3"
    }
*/
```

---

## Build Data Schema

The data schema needs to be built for the initial use of MySQL and SQLite drivers.

This API can be utilized for this purpose.

```php
$cache->rebuild();
```

Or build it manually.

### MySQL

```sql
CREATE TABLE IF NOT EXISTS `cache_data` (
    `cache_key` varchar(40) NOT NULL,
    `cache_value` longtext,
    PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### SQLite

```sql
CREATE TABLE IF NOT EXISTS cache_data (
    cache_key VARCHAR(40) PRIMARY KEY,
    cache_value LONGTEXT
);
```

---

## Garbage Collection

For built-in drivers, enabling garbage collection will automatically clear expired cache from your system.

Use the following parameters:

```php
$config = [
    'gc_enable'      => true,
    'gc_divisor'     => 100, // default
    'gc_probability' => 1, // default
];
```

This implies a 1% probability of executing garbage collection.
Avoid setting it to 100% as it will unnecessarily fetch and check all keys one by one.


Example:
```php
$driver = new \Shieldon\SimpleCache\Cache('file', [
    'storage'   => __DIR__ . '/../tmp',
    'gc_enable' => true,
]);
```

You can just use the `gc_enable` to enable garbage collection.

### Note

For the **Redis**, **Memcache**, and **Memcached** drivers, this method isn't necessary as expired items are automatically cleared.

---

## Author

- [Terry L.](https://terryl.in/) from Tainan, Taiwan.


#### The Origin of this Library

This PHP library was created for the [12th Ironman Game](https://ithelp.ithome.com.tw/2020-12th-ironman) competition, hosted by ITHelp, a Taiwanese IT community. My chosen topic was "Road to PHP Master - The Best Practice in Open Source Code", composed in traditional Chinese. You can read it [here](https://ithelp.ithome.com.tw/users/20111119/ironman/3269) if you're interested.

## License

MIT
