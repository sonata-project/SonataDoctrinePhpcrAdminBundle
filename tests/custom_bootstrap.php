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
