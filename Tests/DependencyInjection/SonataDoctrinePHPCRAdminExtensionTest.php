<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\SonataDoctrinePHPCRAdminExtension;

class SonataDoctrinePHPCRAdminExtensionTest extends AbstractExtensionTestCase
{
    public function getContainerExtensions()
    {
        return array(
           new SonataDoctrinePHPCRAdminExtension(),
        );
    }

    public function testDocumentTreeDefaultValues()
    {
        $this->container->setParameter(
            'kernel.bundles',
            array()
        );
        $this->load(array('document_tree' => array()));

        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.configuration',
            array(
                'routing_defaults' => array(),
                'repository_name' => null,
                'sortable_by' => 'position',
                'move' => true,
                'reorder' => true,
            )
        );
    }

    public function testPrependDefaultRepositoryName()
    {
        $this->container->setParameter('kernel.bundles', array('CmfResourceBundle'));
        $this->container->getExtensionConfig('sonata_doctrine_phpcr_admin');
        $this->container->prependExtensionConfig('sonata_doctrine_phpcr_admin', array('document_tree' => array()));
        $this->container->prependExtensionConfig('cmf_resource', array('default_repository' => 'default'));

        $this->container->getExtension('sonata_doctrine_phpcr_admin')->prepend($this->container);

        $config = $this->container->getExtensionConfig('sonata_doctrine_phpcr_admin');
        $expectedConfig = array(
            'routing_defaults' => array(),
            'repository_name' => 'default',
            'sortable_by' => 'position',
            'enabled' => true,
        );

        $this->assertEquals($expectedConfig, $config[0]['document_tree']);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testPrependDefaultRepositoryNameThrowsOnNonExistingResourceBundle()
    {
        $this->container->setParameter('kernel.bundles', array());
        $this->container->getExtensionConfig('sonata_doctrine_phpcr_admin');
        $this->container->prependExtensionConfig('sonata_doctrine_phpcr_admin', array('document_tree' => array()));
        $this->container->prependExtensionConfig('cmf_resource', array('default_repository' => 'default'));

        $this->container->getExtension('sonata_doctrine_phpcr_admin')->prepend($this->container);
    }

    public function testPrependDefaultRepositoryNameKeepsCustomNames()
    {
        $this->container->setParameter('kernel.bundles', array('CmfResourceBundle'));
        $this->container->getExtensionConfig('sonata_doctrine_phpcr_admin');
        $this->container->prependExtensionConfig(
            'sonata_doctrine_phpcr_admin',
            array('document_tree' => array('repository_name' => 'custom'))
        );
        $this->container->prependExtensionConfig('cmf_resource', array('default_repository' => 'default'));

        $this->container->getExtension('sonata_doctrine_phpcr_admin')->prepend($this->container);

        $config = $this->container->getExtensionConfig('sonata_doctrine_phpcr_admin');
        $expectedConfig = array('repository_name' => 'custom');

        $this->assertEquals($expectedConfig, $config[0]['document_tree']);
    }
}
