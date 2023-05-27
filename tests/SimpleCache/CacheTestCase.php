<?php
/*
 * This file is part of the Shieldon Simple Cache package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\SimpleCache;

use PHPUnit\Framework\TestCase;

class CacheTestCase extends TestCase
{
    public function console($message)
    {
        echo "\n";
        echo "\033[30;104m" . str_pad($message, 40, ' ') . "\033[0m";
        echo "\n";
    }
}
