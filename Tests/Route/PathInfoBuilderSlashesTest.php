<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Filter;

use Sonata\DoctrinePHPCRAdminBundle\Route\PathInfoBuilderSlashes;

class PathInfoBuilderSlashesTest extends \PHPUnit_Framework_TestCase
{
    function testBuild()
    {
        $collectionChild = $this->getMock('Sonata\AdminBundle\Route\RouteCollection', array(), array(), '', false);

        $adminChild = $this->getMock('Sonata\AdminBundle\Admin\Admin', array(), array(), '', false);
        $adminChild->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($collectionChild));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\Admin', array(), array(), '', false);
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue(array($adminChild)));

        $collection = $this->getMock('Sonata\AdminBundle\Route\RouteCollection', array(), array(), '', false);
        $collection->expects($this->once())
            ->method('addCollection')
            ->with($this->anything());
        $collection->expects($this->exactly(6))
            ->method('add')
            ->with($this->anything());
        
        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
}
