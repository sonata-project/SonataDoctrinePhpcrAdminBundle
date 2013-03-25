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

use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\DateFilter;

class DateFilterTest extends \PHPUnit_Framework_TestCase
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
        $this->expr1 = $this->getMock('Doctrine\Common\Collections\Expr\Expression');
        $this->expr2 = $this->getMock('Doctrine\Common\Collections\Expr\Expression');
        $this->compositeExpr = $this->getMock('Doctrine\Common\Collections\Expr\Expression');
        $this->filter = new DateFilter();
    }

    // @todo: Can problaby factor the following 4 test cases into a common class
    //        IF we introduce another test with the same need.

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

    public function getFilters()
    {
        return array(
            array('gte', DateType::TYPE_GREATER_EQUAL),
            array('gt', DateType::TYPE_GREATER_THAN),
            array('lte', DateType::TYPE_LESS_EQUAL),
            array('lt', DateType::TYPE_LESS_THAN),
            array('eq', DateType::TYPE_NULL, null),
            array('neq', DateType::TYPE_NOT_NULL, null),
            // test ChoiceTYPE::TYPE_EQUAL seperately, special case.
        );
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = '__null__')
    {
        $value = new \DateTime('2013/01/16 00:00:00');

        if ($expectedValue == '__null__') {
            $expectedValue = new \DateTime('2013/01/16 00:00:00');
        }
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($this->exprBuilder));
        $this->exprBuilder->expects($this->once())
            ->method($operatorMethod)
            ->with('somefield', $expectedValue)
            ->will($this->returnValue($this->expr1));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with($this->expr1);

        $this->filter->filter(
            $this->proxyQuery, 
            null, 
            'somefield', 
            array('type' => $choiceType, 'value' => $value)
        );
        $this->assertTrue($this->filter->isActive());
    }

    public function testFilterEquals()
    {
        $from = new \DateTime('2013/01/16 00:00:00');
        $to = new \DateTime('2013/01/16 23:59:59');

        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('expr')
            ->will($this->returnValue($this->exprBuilder));

        $this->exprBuilder->expects($this->at(2))
            ->method('andX')
            ->with($this->expr1, $this->expr2)
            ->will($this->returnValue($this->compositeExpr));
        $this->exprBuilder->expects($this->at(0))
            ->method('gte')
            ->with('somefield', $from)
            ->will($this->returnValue($this->expr2));
        $this->exprBuilder->expects($this->at(1))
            ->method('lte')
            ->with('somefield', $to)
            ->will($this->returnValue($this->expr1));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->with($this->expr1);

        $this->filter->filter(
            $this->proxyQuery, 
            null, 
            'somefield', 
            array('type' => DateType::TYPE_EQUAL, 'value' => $from)
        );
        $this->assertTrue($this->filter->isActive());
    }
}
