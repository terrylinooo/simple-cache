# PSR-16 Simple Cache

![build](https://github.com/terrylinooo/simple-cache/workflows/build/badge.svg?branch=master) [![codecov](https://codecov.io/gh/terrylinooo/simple-cache/branch/master/graph/badge.svg)](https://codecov.io/gh/terrylinooo/simple-cache)

PSR-16 simple cache drivers for PHP.

## Install

```bash
composer require shieldon/simple-cache
```

## Usage

### `Cache`

Class `Cache` is a cache adapter that not only allows the implemented instance of `Psr\SimpleCache\CacheInterface` but also has built-in drivers already.

Built-in drivers: `file`, `redis`, `mysql`, `sqlite`.

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
    'charset' => 'utf8'
];
```

*Sqlite*
```php
$config = [
    'storage' => '/tmp/simple-cache',
];
```

## API

- get
- set
- has
- delete
- getMultiple
- setMultiple
- deleteMultiple

## Author

- [Terry L.](https://terryl.in/) from Tainan, Taiwan.

## License

MIT



