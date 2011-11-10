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

use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;

class dummyMetaData
{
    public $nodeType = "some_type";
}

class ProxyQueryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->qf = $this->getMock('PHPCR\Query\QOM\QueryObjectModelFactoryInterface', array(), array());
        $this->qb = $this->getMockBuilder('PHPCR\Util\QOM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructor()
    {
        $pq = new ProxyQuery($this->qf, $this->qb);
        $this->assertEquals($this->qb, $pq->getQueryBuilder());
    }

    public function testSetSortBy()
    {
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setSortBy('field');
        $this->assertEquals('field', $pq->getSortBy());
    }

    public function testSetSortOrder()
    {
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setSortOrder('SOME_ORDER');
        $this->assertEquals('SOME_ORDER', $pq->getSortOrder());
    }

    public function testSetFirstResult()
    {
        $this->qb->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(19));

        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setFirstResult(19);
    }

    public function testGetFirstResult()
    {
        $this->qb->expects($this->once())
            ->method('getFirstResult');

        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->getFirstResult();
    }

    public function testSetMaxResults()
    {
        $this->qb->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo(29));

        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setMaxResults(29);
    }

    public function testGetMaxResults()
    {
        $this->qb->expects($this->once())
            ->method('getMaxResults');

        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->getMaxResults();
    }

    public function testExecute()
    {
        $source = $this->getMock('PHPCR\Query\QOM\SourceInterface', array(), array());
        $this->qf->expects($this->once())
            ->method('selector')
            ->with($this->anything())
            ->will($this->returnValue($source));
        $dynamic_operand = $this->getMock('PHPCR\Query\QOM\DynamicOperandInterface', array(), array());
        $static_operand = $this->getMock('PHPCR\Query\QOM\StaticOperandInterface', array(), array());
        $this->qf->expects($this->any())
            ->method('propertyValue')
            ->with($this->anything())
            ->will($this->returnValue($dynamic_operand));
        $constraint = $this->getMock('PHPCR\Query\QOM\ConstraintInterface', array(), array());
        $this->qf->expects($this->once())
            ->method('comparison')
            ->with($this->anything())
            ->will($this->returnValue($constraint));
        $this->qf->expects($this->once())
            ->method('literal')
            ->with($this->anything())
            ->will($this->returnValue($static_operand));
        $this->qb->expects($this->once())
            ->method('from')
            ->with($this->anything());
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with($this->anything());
        $dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $dm->expects($this->once())
            ->method('getClassMetadata')
            ->with("some_document_name")
            ->will($this->returnValue(new dummyMetaData()));
        $query = $this->getMockBuilder('Jackalope\Query\QueryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())
            ->method('getNodes')
            ->will($this->returnValue(array()));
        $this->qb->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($query));
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setDocumentManager($dm);
        $pq->setDocumentName("some_document_name");
        $pq->execute();
    }

}
