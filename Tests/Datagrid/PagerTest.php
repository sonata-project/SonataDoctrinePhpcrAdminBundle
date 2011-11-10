<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Datagrid;

use Sonata\DoctrinePHPCRAdminBundle\Datagrid\Pager;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pager = new Pager(10);
    }

    public function testInitNumPages()
    {
        $proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(10));
        $proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));
        $proxyQuery->expects($this->once())
            ->method('execute')
            ->with($this->anything())
            ->will($this->returnValue(range(0, 12)));

        $this->pager->setQuery($proxyQuery);
        $this->pager->init();
        $this->AssertEquals(2, $this->pager->getLastPage());
    }

    public function testInitOffset()
    {
        $proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(10));
        //Asserting that the offset will be set correctly
        $proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(10));
        $proxyQuery->expects($this->once())
            ->method('execute')
            ->with($this->anything())
            ->will($this->returnValue(range(0, 12)));

        $this->pager->setQuery($proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();
        $this->AssertEquals(2, $this->pager->getLastPage());
    }

    public function testNoPagesPerConfig()
    {
        $proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(0));
        $proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));
        $proxyQuery->expects($this->once())
            ->method('execute')
            ->with($this->anything())
            ->will($this->returnValue(range(0, 12)));

        $this->pager->setQuery($proxyQuery);
        //Max per page 0 means no pagination
        $this->pager->setMaxPerPage(0);
        $this->pager->init();
        $this->AssertEquals(0, $this->pager->getLastPage());
    }

    public function testNoPagesPerNoResults()
    {
        $proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(0));
        $proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));
        $proxyQuery->expects($this->once())
            ->method('execute')
            ->with($this->anything())
            //No results means no pagination
            ->will($this->returnValue(array()));

        $this->pager->setQuery($proxyQuery);
        $this->pager->init();
        $this->AssertEquals(0, $this->pager->getLastPage());
    }
    public function testInitNoQuery()
    {
        $this->setExpectedException('RuntimeException');
        $this->pager->init();
    }
}
