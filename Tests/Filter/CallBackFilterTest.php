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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrinePHPCRAdminBundle\Filter\CallbackFilter;

class CallbackFilterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->qb = $this->getMockBuilder('Doctrine\ODM\PHPCR\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->exprBuilder = $this->getMock('Doctrine\ODM\PHPCR\Query\ExpressionBuilder');
        $this->expr = $this->getMock('Doctrine\Common\Collections\Expr\Expression');
    }

    public function testFilterNullData()
    {
        $filter = new CallbackFilter();

        $filter->initialize('field_name', array('callback' => function() { return; }));
        $res = $filter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertNull($res);
        $this->assertFalse($filter->isActive());
    }

    public function testFilterEmptyArrayData()
    {
        $filter = new CallbackFilter();

        $filter->initialize('field_name', array('callback' => function() { return; }));
        $res = $filter->filter($this->proxyQuery, null, 'somefield', array());
        $this->assertNull($res);
        $this->assertFalse($filter->isActive());
    }

    public function testFilterMethod()
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($this->exprBuilder));
        $this->exprBuilder->expects($this->once())
            ->method('eq')
            ->with('somefield', 'someValue')
            ->will($this->returnValue($this->expr));

        $filter = new CallbackFilter();
        $filter->initialize('field_name', array(
            'callback' => array($this, 'callbackMethod')
        ));

        $filter->filter($this->proxyQuery, null, 'somefield', array('type' => '', 'value' => 'someValue'));
        $this->assertTrue($filter->isActive());
    }

    public function callbackMethod(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $queryBuilder = $proxyQuery->getQueryBuilder();
        $eb = $queryBuilder->expr();

        $queryBuilder->andWhere($eb->eq($field, $data['value']));

        return true;
    }

    public function testFilterClosure()
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($this->exprBuilder));
        $this->exprBuilder->expects($this->once())
            ->method('eq')
            ->with('somefield', 'someValue')
            ->will($this->returnValue($this->expr));

        $filter = new CallbackFilter();
        $filter->initialize('field_name', array(
            'callback' => function (ProxyQueryInterface $proxyQuery, $alias, $field, $data) {
                if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
                    return;
                }

                $queryBuilder = $proxyQuery->getQueryBuilder();
                $eb = $queryBuilder->expr();

                $queryBuilder->andWhere($eb->eq($field, $data['value']));

                return true;
            }
        ));
        $filter->filter($this->proxyQuery, null, 'somefield', array('type' => '', 'value' => 'someValue'));
        $this->assertTrue($filter->isActive());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWithoutCallback()
    {
        $filter = new CallbackFilter();

        $filter->setOption('callback', null);
        $filter->filter($this->proxyQuery, null, 'somefield', null);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCallbackNotCallable()
    {
        $filter = new CallbackFilter();

        $filter->setOption('callback', 'someCallback');
        $filter->filter($this->proxyQuery, null, 'somefield', null);
    }
}