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
    protected function setUp(): void
    {
        $this->pager = new Pager(10);

        $this->proxyQuery = $this->createMock(ProxyQuery::class);
    }

    public function testInitNumPages(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->willReturn(range(0, 12));

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(10));

        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->init();

        static::assertSame(2, $this->pager->getLastPage());
    }

    public function testInitOffset(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->willReturn(range(0, 12));

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(10));

        // Asserting that the offset will be set correctly
        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(10));

        $this->pager->setQuery($this->proxyQuery);
        $this->pager->setPage(2);
        $this->pager->init();

        static::assertSame(2, $this->pager->getLastPage());
    }

    public function testNoPagesPerConfig(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->willReturn([]);

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(0));

        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(0));

        $this->pager->setQuery($this->proxyQuery);

        // Max per page 0 means no pagination
        $this->pager->setMaxPerPage(0);
        $this->pager->init();

        static::assertSame(0, $this->pager->getLastPage());
    }

    public function testNoPagesForNoResults(): void
    {
        $this->proxyQuery->expects(static::once())
            ->method('execute')
            ->with([], PHPCRQuery::HYDRATE_PHPCR)
            ->willReturn([]);

        $this->proxyQuery->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(0));
        $this->proxyQuery->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(0));

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
