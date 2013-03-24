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

use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\NumberFilter;

class NumberFilterTest extends \PHPUnit_Framework_TestCase
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
        $this->filter = new NumberFilter();
    }

    public function testFilterNullData()
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData()
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', array());
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType()
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => NumberType::TYPE_EQUAL));
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue()
    {
        $this->proxyQuery->expects($this->never())
            ->method('andWhere');

        $this->qb->expects($this->never())
            ->method('andWhere');

        $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => NumberType::TYPE_EQUAL, 'value' => ' '));
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return array(
            array('gte', NumberType::TYPE_GREATER_EQUAL, 2),
            array('gt', NumberType::TYPE_GREATER_THAN, 3),
            array('lte', NumberType::TYPE_LESS_EQUAL, 4),
            array('lt', NumberType::TYPE_LESS_THAN, 5),
            array('eq', NumberType::TYPE_EQUAL, 6),
            array('eq', 'default', 7),
        );
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = 'somevalue')
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($this->exprBuilder));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnValue($this->qb));
        $this->exprBuilder->expects($this->once())
            ->method($operatorMethod)
            ->with('somefield', $expectedValue)
            ->will($this->returnValue($this->expr));

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            array('type' => $choiceType, 'value' => $expectedValue)
        );
        $this->assertTrue($this->filter->isActive());
    }
}