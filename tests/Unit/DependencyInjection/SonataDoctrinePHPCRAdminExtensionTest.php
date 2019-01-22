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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\SonataDoctrinePHPCRAdminExtension;

class SonataDoctrinePHPCRAdminExtensionTest extends AbstractExtensionTestCase
{
    public function getContainerExtensions()
    {
        return [
           new SonataDoctrinePHPCRAdminExtension(),
        ];
    }

    public function testDocumentTreeDefaultValues(): void
    {
        $this->container->setParameter(
            'kernel.bundles',
            []
        );
        $this->load(['document_tree' => []]);

        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.configuration',
            [
                'routing_defaults' => [],
                'repository_name' => null,
                'sortable_by' => 'position',
                'move' => true,
                'reorder' => true,
            ]
        );

        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.routing_defaults',
            []
        );
        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.repository_name',
            null
        );
        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.sortable_by',
            'position'
        );
        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.move',
            true
        );
        $this->assertContainerBuilderHasParameter(
            'sonata_admin_doctrine_phpcr.tree_block.reorder',
            true
        );
    }
}
