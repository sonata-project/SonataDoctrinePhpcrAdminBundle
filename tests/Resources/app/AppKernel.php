<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->registerBundleSet('sonata_admin_phpcr', [
            Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class,
            Knp\Bundle\MenuBundle\KnpMenuBundle::class,
            Sonata\AdminBundle\SonataAdminBundle::class,
            Sonata\CoreBundle\SonataCoreBundle::class,
            Sonata\BlockBundle\SonataBlockBundle::class,
            Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle::class,
            Symfony\Bundle\TwigBundle\TwigBundle::class,
            Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle::class,
        ]);

        $this->requireBundleSet('default');

        $this->requireBundleSets([
            'phpcr_odm',
            'sonata_admin_phpcr',
        ]);

        $this->addBundles([
            new Symfony\Cmf\Bundle\ResourceBundle\CmfResourceBundle(),
            new Symfony\Cmf\Bundle\ResourceRestBundle\CmfResourceRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
        ]);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.php');
        $loader->load(__DIR__.'/config/admin-test.xml');
    }
}
