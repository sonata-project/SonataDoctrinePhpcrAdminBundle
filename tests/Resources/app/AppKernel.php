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

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSet('default');

        $this->requireBundleSets(['phpcr_odm']);

        $this->addBundles([
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle(),
            new Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle(),
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
