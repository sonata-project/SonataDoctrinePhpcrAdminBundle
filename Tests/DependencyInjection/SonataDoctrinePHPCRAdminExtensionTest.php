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
                'routing_defaults' => 'routing_defaults',
                'repository_name' => 'repository_name',
                'sortable_by' => 'sortable_by',
                'move' => false,
                'reorder' => false,
            )
        );
    }

    public function testDocumentTreeEnableMoveAndReorder()
    {
        $this->container->setParameter(
            'kernel.bundles',
            array('move' => array('reorder' => true))
        );
        $this->load(array('document_tree' => array()));

        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.configuration',
            array(
                'move' => true,
                'reorder' => true,
            )
        );
    }
}
