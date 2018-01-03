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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Route;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrinePHPCRAdminBundle\Route\PathInfoBuilderSlashes;

class PathInfoBuilderSlashesTest extends TestCase
{
    public function testBuild(): void
    {
        $collectionChild = $this->createMock(RouteCollection::class);

        $adminChild = $this->createMock(AbstractAdmin::class);
        $adminChild->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($collectionChild));

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue([$adminChild]));

        $collection = $this->createMock(RouteCollection::class);
        $collection->expects($this->once())
            ->method('addCollection')
            ->with($this->anything());
        $collection->expects($this->exactly(7))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }

    public function testBuildWithAcl(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue([]));
        $admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $collection = $this->createMock(RouteCollection::class);
        $collection->expects($this->exactly(8))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
}
