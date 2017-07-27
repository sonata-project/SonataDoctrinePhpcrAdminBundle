<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$autoload_paths = array_filter(array(
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../../vendor/autoload.php',
), 'file_exists');

if (!$autoload_paths) {
    throw new RuntimeException('Run "composer install" to run test suite.');
}

require_once current($autoload_paths);
