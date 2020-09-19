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

/**
 * Register to PSR-4 autoloader.
 *
 * @return void
 */
function simplecache_register()
{
    spl_autoload_register('simplecache_autoload', true, false);
}

/**
 * PSR-4 autoloader.
 *
 * @param string $className
 * 
 * @return void
 */
function simplecache_autoload($className)
{
    $prefix = 'Shieldon\\SimpleCache\\';
    $dir = __DIR__ . '/src/SimpleCache';

    if (0 === strpos($className, $prefix)) {
        $parts = explode('\\', substr($className, strlen($prefix)));
        $filepath = $dir . '/' . implode('/', $parts) . '.php';

        if (is_file($filepath)) {
            require $filepath;
        }
    }
}

simplecache_register();