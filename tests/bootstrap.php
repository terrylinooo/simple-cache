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

define('BOOTSTRAP_DIR', __DIR__);

/**
 * Create a writable directrory for unit testing.
 *
 * @param string $filename File name.
 *
 * @return string The file's path.
 */
function get_testing_file_path(string $filename, string $dir = ''): string
{
    $dir = BOOTSTRAP_DIR . '/../tmp';

    if ($dir === '') {
        $dir = BOOTSTRAP_DIR . '/../tmp/' . $dir;
    }

    if (!is_dir($dir)) {
        $originalUmask = umask(0);
        @mkdir($dir, 0777, true);
        umask($originalUmask);
    }

    return $dir . '/' . $filename;
}
