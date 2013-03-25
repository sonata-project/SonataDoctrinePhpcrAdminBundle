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

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\ChoiceFilter;

class ChoiceFilterTest extends \PHPUnit_Framework_TestCase
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
        $this->filter = new ChoiceFilter();
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
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL));
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function getMeaninglessValues()
    {
        return array(
            array('  '),
            array(null),
            array(false),
            array('all'),
            array(array()),
            array(array('', 'all')),
        );
    }

    /**
     * @dataProvider getMeaninglessValues
     */
    public function testFilterEmptyArrayDataWithMeaninglessValue($value)
    {
        $this->proxyQuery->expects($this->never())
            ->method('andWhere');

        $this->qb->expects($this->never())
            ->method('andWhere');

        $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => $value));
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return array(
            array('eq', ChoiceType::TYPE_EQUAL),
            array('textSearch', ChoiceType::TYPE_NOT_CONTAINS, '* -somevalue'),
            array('like', ChoiceType::TYPE_CONTAINS, '%somevalue%'),
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
            array('type' => $choiceType, 'value' => 'somevalue')
        );
        $this->assertTrue($this->filter->isActive());
    }

    public function getFiltersMultiple()
    {
        return array(
            array('textSearch', ChoiceType::TYPE_NOT_CONTAINS, array('somevalue'), '* -somevalue'),
            array('textSearch', ChoiceType::TYPE_NOT_CONTAINS, array('somevalue', 'somevalue'), '* -somevalue'),
            array('like', ChoiceType::TYPE_CONTAINS, array('somevalue'), '%somevalue%'),
            array('like', ChoiceType::TYPE_CONTAINS, array('somevalue', 'somevalue'), '%somevalue%'),
            array('like', ChoiceType::TYPE_EQUAL, array('somevalue'), '%somevalue%'),
            array('like', ChoiceType::TYPE_EQUAL, array('somevalue', 'somevalue'), '%somevalue%'),
        );
    }

    /**
     * @dataProvider getFiltersMultiple
     */
    public function testFilterMultipleSwitch($operatorMethod, $choiceType, $value, $expectedValue)
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
        $this->qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnValue($this->qb));
        $this->exprBuilder->expects($this->exactly(count($value)))
            ->method($operatorMethod)
            ->with('somefield', $expectedValue)
            ->will($this->returnValue($this->expr));

        if (count($value) > 1) {
            $this->qb->expects($this->exactly(count($value) + 1))
                ->method('expr')
                ->will($this->returnValue($this->exprBuilder));

            if ($choiceType === ChoiceType::TYPE_NOT_CONTAINS) {
                $this->exprBuilder->expects($this->once())
                    ->method('andX')
                    ->with($this->isInstanceOf('Doctrine\Common\Collections\Expr\Expression'))
                    ->will($this->returnValue($this->expr));
            } else {
                $this->exprBuilder->expects($this->once())
                    ->method('orX')
                    ->with($this->isInstanceOf('Doctrine\Common\Collections\Expr\Expression'))
                    ->will($this->returnValue($this->expr));
            }
        } else {
            $this->qb->expects($this->exactly(count($value)))
                ->method('expr')
                ->will($this->returnValue($this->exprBuilder));
        }

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            array('type' => $choiceType, 'value' => $value)
        );
        $this->assertTrue($this->filter->isActive());
    }
}