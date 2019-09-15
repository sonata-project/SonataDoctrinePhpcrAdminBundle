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

    public function setUp(): void
    {
        $this->qb = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->pq = new ProxyQuery($this->qb, 'a');
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(QueryBuilder::class, $this->pq->getQueryBuilder());
    }

    public function testSetSortBy(): void
    {
        $this->pq->setSortBy([], ['fieldName' => 'field']);
        $this->assertSame('field', $this->pq->getSortBy());
    }

    public function testSetSortOrder(): void
    {
        $this->pq->setSortOrder('ASC');
        $this->assertSame('ASC', $this->pq->getSortOrder());
    }

    public function testSetSortOrderInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->pq->setSortOrder('SOME_ORDER');
        $this->assertSame('SOME_ORDER', $this->pq->getSortOrder());
    }

    public function testSetFirstResult(): void
    {
        $this->qb->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(19));

        $this->pq->setFirstResult(19);
    }

    public function testGetFirstResult(): void
    {
        $this->qb->expects($this->once())
            ->method('getFirstResult');

        $this->pq->getFirstResult();
    }

    public function testSetMaxResults(): void
    {
        $this->qb->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(29));

        $this->pq->setMaxResults(29);
    }

    public function testGetMaxResults(): void
    {
        $this->qb->expects($this->once())
            ->method('getMaxResults');

        $this->pq->getMaxResults();
    }

    public function testExecute(): void
    {
        $this->qb->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);
        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn('test');

        $res = $this->pq->execute();
        $this->assertSame('test', $res);
    }

    public function testGetAndSetDocumentManager(): void
    {
        $dm = $this->createMock(DocumentManager::class);
        $this->pq->setDocumentManager($dm);
        $this->assertSame($dm, $this->pq->getDocumentManager());
    }
}
