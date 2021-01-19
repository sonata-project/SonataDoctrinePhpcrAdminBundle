<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Sonata\DoctrinePHPCRAdminBundle\Tests\Fixtures\App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/*
 * fix encoding issue while running text on different host with different locale configuration
 */
setlocale(\LC_ALL, 'en_US.UTF-8');

if (file_exists($file = __DIR__.'/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/autoload.php.dist')) {
    require_once $file;
}

/*
 * try to get Symfony's PHPUnit Bridge
 */
$files = array_filter([
    __DIR__.'/../vendor/symfony/symfony/src/Symfony/Bridge/PhpUnit/bootstrap.php',
    __DIR__.'/../vendor/symfony/phpunit-bridge/bootstrap.php',
    __DIR__.'/../../../../vendor/symfony/symfony/src/Symfony/Bridge/PhpUnit/bootstrap.php',
    __DIR__.'/../../../../vendor/symfony/phpunit-bridge/bootstrap.php',
], 'file_exists');

if ($files) {
    require_once current($files);
}

require_once __DIR__.'/../vendor/symfony-cmf/testing/bootstrap/bootstrap.php';

$application = new Application(new Kernel());
$application->setAutoExit(false);

// Load fixtures of the AppTestBundle
$input = new ArrayInput([
    'command' => 'doctrine:phpcr:init:dbal',
    '--drop' => true,
    '--force' => true,
]);
$application->run($input, new ConsoleOutput());

$input = new ArrayInput([
    'command' => 'doctrine:phpcr:repository:init',
]);
$application->run($input, new ConsoleOutput());
