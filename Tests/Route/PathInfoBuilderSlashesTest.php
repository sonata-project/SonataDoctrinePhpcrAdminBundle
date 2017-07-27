<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Route;

use Sonata\DoctrinePHPCRAdminBundle\Route\PathInfoBuilderSlashes;

class PathInfoBuilderSlashesTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $collectionChild = $this->createMock('Sonata\\AdminBundle\\Route\\RouteCollection');

        $adminChild = $this->createMock('Sonata\\AdminBundle\\Admin\\AbstractAdmin');
        $adminChild->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($collectionChild));

        $admin = $this->createMock('Sonata\\AdminBundle\\Admin\\AbstractAdmin');
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue(array($adminChild)));

        $collection = $this->createMock('Sonata\\AdminBundle\\Route\\RouteCollection');
        $collection->expects($this->once())
            ->method('addCollection')
            ->with($this->anything());
        $collection->expects($this->exactly(7))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }

    public function testBuildWithAcl()
    {
        $admin = $this->createMock('Sonata\\AdminBundle\\Admin\\AbstractAdmin');
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue(array()));
        $admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $collection = $this->createMock('Sonata\\AdminBundle\\Route\\RouteCollection');
        $collection->expects($this->exactly(8))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
}
