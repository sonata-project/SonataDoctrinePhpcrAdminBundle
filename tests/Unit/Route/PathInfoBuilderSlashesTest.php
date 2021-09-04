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
        $adminChild->expects(static::once())
            ->method('getRoutes')
            ->willReturn($collectionChild);

        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects(static::once())
            ->method('getChildren')
            ->willReturn([$adminChild]);

        $collection = $this->createMock(RouteCollection::class);
        $collection->expects(static::once())
            ->method('addCollection')
            ->with(static::anything());
        $collection->expects(static::exactly(7))
            ->method('add')
            ->with(static::anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }

    public function testBuildWithAcl(): void
    {
        $admin = $this->createMock(AbstractAdmin::class);
        $admin->expects(static::once())
            ->method('getChildren')
            ->willReturn([]);
        $admin->expects(static::once())
            ->method('isAclEnabled')
            ->willReturn(true);

        $collection = $this->createMock(RouteCollection::class);
        $collection->expects(static::exactly(8))
            ->method('add')
            ->with(static::anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
}
