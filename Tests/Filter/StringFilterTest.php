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

use Sonata\DoctrinePHPCRAdminBundle\Filter\StringFilter;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

class StringFilterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->proxyQuery = $this->getMockBuilder('Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $this->qb = $this->getMockBuilder('Doctrine\ODM\PHPCR\Query\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->expr = $this->getMock('Doctrine\ODM\PHPCR\Query\ExpressionBuilder');
        $this->stringFilter = new StringFilter();
    }

    public function testFilterNullData()
    {
        $res = $this->stringFilter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertNull($res);
    }

    public function testFilterEmptyArrayData()
    {
        $res = $this->stringFilter->filter($this->proxyQuery, null, 'somefield', array());
        $this->assertNull($res);
    }

    public function testFilterEmptyArrayDataSpecifiedType()
    {
        $res = $this->stringFilter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL));
        $this->assertNull($res);
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue()
    {
        $this->proxyQuery->expects($this->never())
            ->method('andWhere');
        
        $this->stringFilter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => ' '));
    }

    public function getFilters()
    {
        return array(
            array('eq', ChoiceType::TYPE_EQUAL),
            array('textSearch', ChoiceType::TYPE_NOT_CONTAINS, '* -somevalue'),
            array('like', ChoiceType::TYPE_CONTAINS, '%somevalue'),
            array('textSearch', ChoiceType::TYPE_CONTAINS_WORDS),
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
            ->will($this->returnValue($this->expr));
        $this->expr->expects($this->once())
            ->method($operatorMethod)
            ->with('somefield', $expectedValue)
            ->will($this->returnValue($this->expr));

        $this->stringFilter->filter(
            $this->proxyQuery, 
            null, 
            'somefield', 
            array('type' => $choiceType, 'value' => 'somevalue')
        );
    }
}
