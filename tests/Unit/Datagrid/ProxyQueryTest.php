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

    public function setUp()
    {
        $this->qb = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->pq = new ProxyQuery($this->qb, 'a');
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(QueryBuilder::class, $this->pq->getQueryBuilder());
    }

    public function testSetSortBy()
    {
        $this->pq->setSortBy([], ['fieldName' => 'field']);
        $this->assertEquals('field', $this->pq->getSortBy());
    }

    public function testSetSortOrder()
    {
        $this->pq->setSortOrder('ASC');
        $this->assertEquals('ASC', $this->pq->getSortOrder());
    }

    public function testSetSortOrderInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->pq->setSortOrder('SOME_ORDER');
        $this->assertEquals('SOME_ORDER', $this->pq->getSortOrder());
    }

    public function testSetFirstResult()
    {
        $this->qb->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(19));

        $this->pq->setFirstResult(19);
    }

    public function testGetFirstResult()
    {
        $this->qb->expects($this->once())
            ->method('getFirstResult');

        $this->pq->getFirstResult();
    }

    public function testSetMaxResults()
    {
        $this->qb->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(29));

        $this->pq->setMaxResults(29);
    }

    public function testGetMaxResults()
    {
        $this->qb->expects($this->once())
            ->method('getMaxResults');

        $this->pq->getMaxResults();
    }

    public function testExecute()
    {
        $this->qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($this->query));
        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue('test'));

        $res = $this->pq->execute();
        $this->assertEquals('test', $res);
    }

    public function testGetAndSetDocumentManager()
    {
        $dm = $this->createMock(DocumentManager::class);
        $this->pq->setDocumentManager($dm);
        $this->assertEquals($dm, $this->pq->getDocumentManager());
    }
}
