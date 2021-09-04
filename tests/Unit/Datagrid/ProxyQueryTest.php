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

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;

class ProxyQueryTest extends TestCase
{
    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $qb;

    /**
     * @var ProxyQuery
     */
    private $pq;

    protected function setUp(): void
    {
        $this->qb = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->pq = new ProxyQuery($this->qb, 'a');
    }

    public function testConstructor(): void
    {
        static::assertInstanceOf(QueryBuilder::class, $this->pq->getQueryBuilder());
    }

    public function testSetSortBy(): void
    {
        $this->pq->setSortBy([], ['fieldName' => 'field']);
        static::assertSame('field', $this->pq->getSortBy());
    }

    public function testSetSortOrder(): void
    {
        $this->pq->setSortOrder('ASC');
        static::assertSame('ASC', $this->pq->getSortOrder());
    }

    public function testSetSortOrderInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->pq->setSortOrder('SOME_ORDER');
        static::assertSame('SOME_ORDER', $this->pq->getSortOrder());
    }

    public function testSetFirstResult(): void
    {
        $this->qb->expects(static::once())
            ->method('setFirstResult')
            ->with(static::equalTo(19));

        $this->pq->setFirstResult(19);
    }

    public function testGetFirstResult(): void
    {
        $this->qb->expects(static::once())
            ->method('getFirstResult');

        $this->pq->getFirstResult();
    }

    public function testSetMaxResults(): void
    {
        $this->qb->expects(static::once())
            ->method('setMaxResults')
            ->with(static::equalTo(29));

        $this->pq->setMaxResults(29);
    }

    public function testGetMaxResults(): void
    {
        $this->qb->expects(static::once())
            ->method('getMaxResults');

        $this->pq->getMaxResults();
    }

    public function testExecute(): void
    {
        $this->qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($this->query);
        $this->query->expects(static::once())
            ->method('execute')
            ->willReturn('test');

        $res = $this->pq->execute();
        static::assertSame('test', $res);
    }

    public function testExecuteWithSortBy(): void
    {
        $qb = $this->createPartialMock(QueryBuilder::class, ['getQuery']);

        $qb->expects(static::once())
            ->method('getQuery')
            ->willReturn($this->query);
        $this->query->expects(static::once())
            ->method('execute')
            ->willReturn('test');

        $pq = new ProxyQuery($qb, 'a');

        $pq->setSortBy([], ['fieldName' => 'field']);
        $pq->setSortOrder('ASC');
        $res = $pq->execute();
        static::assertSame('test', $res);
    }

    public function testGetAndSetDocumentManager(): void
    {
        $dm = $this->createMock(DocumentManager::class);
        $this->pq->setDocumentManager($dm);
        static::assertSame($dm, $this->pq->getDocumentManager());
    }
}
