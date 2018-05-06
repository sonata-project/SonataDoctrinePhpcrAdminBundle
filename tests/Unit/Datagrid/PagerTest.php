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

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Datagrid;

use Doctrine\ODM\PHPCR\Query\Query as PHPCRQuery;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\Pager;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;

class PagerTest extends TestCase
{
    public function setUp(): void
    {
        $this->pager = new Pager(10);

        $this->proxyQuery = $this->getMockBuilder(ProxyQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInitNumPages(): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->will($this->returnValue(range(0, 12)));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(10));

        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();

        $this->assertEquals(2, $this->pager->getLastPage());
    }

    public function testInitOffset(): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->will($this->returnValue(range(0, 12)));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(10));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        $this->assertEquals(2, $this->pager->getLastPage());
    }

    public function testNoPagesPerConfig(): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->will($this->returnValue([]));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(0));

        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $this->pager->setQuery($this->proxyQuery);

        // Max per page 0 means no pagination
        $this->pager->setMaxPerPage(0);
        $this->pager->init();

        $this->assertEquals(0, $this->pager->getLastPage());
    }

    public function testNoPagesForNoResults(): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->will($this->returnValue([]));

        $this->proxyQuery->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(0));
        $this->proxyQuery->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();
        $this->AssertEquals(0, $this->pager->getLastPage());
    }

    public function testInitNoQuery(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->pager->init();
    }
}
