<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Route;

use Sonata\DoctrinePHPCRAdminBundle\Route\PathInfoBuilderSlashes;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;

class PathInfoBuilderSlashesTest extends \PHPUnit_Framework_TestCase
{
    function testBuild()
    {
        $collectionChild = $this->getMock('Sonata\\AdminBundle\\Route\\RouteCollection', array(), array(), '', false);

        $adminChild = $this->getMockBuilder('Sonata\\AdminBundle\\Admin\\Admin')->disableOriginalConstructor()->getMock();
        $adminChild->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue($collectionChild));

        $admin = $this->getMockBuilder('Sonata\\AdminBundle\\Admin\\Admin')->disableOriginalConstructor()->getMock();
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue(array($adminChild)));

        $collection = $this->getMock('Sonata\\AdminBundle\\Route\\RouteCollection', array(), array(), '', false);
        $collection->expects($this->once())
            ->method('addCollection')
            ->with($this->anything());
        $collection->expects($this->exactly(7))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
    
    function testBuildWithAcl()
    {
        $admin = $this->getMockBuilder('Sonata\\AdminBundle\\Admin\\Admin')->disableOriginalConstructor()->getMock();
        $admin->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue(array()));
        $admin->expects($this->once())
            ->method('isAclEnabled')
            ->will($this->returnValue(true));

        $collection = $this->getMock('Sonata\\AdminBundle\\Route\\RouteCollection', array(), array(), '', false);
        $collection->expects($this->exactly(8))
            ->method('add')
            ->with($this->anything());

        $builder = new PathInfoBuilderSlashes();
        $builder->build($admin, $collection);
    }
}
