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
use PHPCR\NodeInterface;
use PHPCR\Query\QueryResultInterface;

class dummyMetaData
{
    public $nodeType = "some_type";
}

class ProxyQueryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->qf = $this->getMock('PHPCR\\Query\\QOM\\QueryObjectModelFactoryInterface', array(), array());
        $this->qb = $this->getMockBuilder('PHPCR\\Util\\QOM\\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testConstructor()
    {
        $pq = new ProxyQuery($this->qf, $this->qb);
        $this->assertInstanceOf('PHPCR\\Util\\QOM\\QueryBuilder', $pq->getQueryBuilder());
        $this->assertInstanceOf('PHPCR\\Query\\QOM\\QueryObjectModelFactoryInterface', $pq->getQueryObjectModelFactory());
    }

    public function testSetSortBy()
    {
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setSortBy(array(), array('fieldName' => 'field'));
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
        $source = $this->getMock('PHPCR\\Query\\QOM\\SourceInterface', array(), array());
        $this->qf->expects($this->once())
            ->method('selector')
            ->with($this->anything())
            ->will($this->returnValue($source));
        $dynamic_operand = $this->getMock('PHPCR\\Query\\QOM\\DynamicOperandInterface', array(), array());
        $static_operand = $this->getMock('PHPCR\\Query\\QOM\\StaticOperandInterface', array(), array());
        $this->qf->expects($this->any())
            ->method('propertyValue')
            ->with($this->anything())
            ->will($this->returnValue($dynamic_operand));
        $constraint = $this->getMock('PHPCR\\Query\\QOM\\ConstraintInterface', array(), array());
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
        $uow = $this->getMock('Doctrine\\ODM\\PHPCR\\UnitOfWork', array(), array(), '', false);
        $dm = $this->getMockBuilder('Doctrine\\ODM\\PHPCR\\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $dm->expects($this->once())
            ->method('getClassMetadata')
            ->with("some_document_name")
            ->will($this->returnValue(new dummyMetaData()));
        $dm->expects($this->exactly(2))
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));
        $query = $this->getMock('Sonata\\DoctrinePHPCRAdminBundle\\Tests\\Datagrid\\MockQueryResult');
        $node = $this->getMock('Sonata\\DoctrinePHPCRAdminBundle\\Tests\\Datagrid\\MockNode');
        $query->expects($this->once())
            ->method('getNodes')
            ->will($this->returnValue(array(
                'somepath1' => $node,
                'somepath2' => $node,
            )));
        $this->qb->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($query));
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setDocumentManager($dm);
        $pq->setDocumentName("some_document_name");
        $pq->execute();
    }

    public function testGetAndSetDocumentName()
    {
        $pq = new ProxyQuery($this->qf, $this->qb);
        $name = 'somename';
        $pq->setDocumentName($name);
        $this->assertEquals($name, $pq->getDocumentName());
    }

    public function testGetAndSetDocumentManager()
    {
        $dm = $this->getMockBuilder('Doctrine\\ODM\\PHPCR\\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->setDocumentManager($dm);
        $this->assertEquals($dm, $pq->getDocumentManager());
    }

    public function testAndWhere()
    {
        $constraint = $this->getMock('PHPCR\\Query\\QOM\\ConstraintInterface');
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with($constraint);
        $pq = new ProxyQuery($this->qf, $this->qb);
        $pq->andWhere($constraint);

    }
}

/**
 * dummy class because mocking NodeInterface is not possible, as Traversable
 * is not directly implementable and PHPUnit is not creative enough.
 */
abstract class MockNode implements \Iterator, NodeInterface {}
abstract class MockQueryResult implements \Iterator, NodeInterface {}