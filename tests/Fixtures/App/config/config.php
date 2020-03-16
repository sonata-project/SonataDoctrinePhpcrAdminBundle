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

use Symfony\Component\HttpKernel\Kernel;

$container->setParameter('cmf_testing.bundle_fqn', 'Sonata\DoctrinePHPCRAdminBundle');
$loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
$loader->import(__DIR__.'/sonata_phpcr_admin.yml');
$loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php');

$container->loadFromExtension('framework', [
    'assets' => null,
]);

if (version_compare(Kernel::VERSION, '4.2', '<')) {
    $container->loadFromExtension('framework', [
        'fragments' => ['enabled' => true],
    ]);
}
