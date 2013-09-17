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

class ChoiceFilterTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
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
            array(ChoiceType::TYPE_NOT_CONTAINS, array('somevalue'), '* -somevalue'),
            array(ChoiceType::TYPE_NOT_CONTAINS, array('somevalue', 'somevalue'), '* -somevalue'),
            array(ChoiceType::TYPE_CONTAINS, array('somevalue'), '%somevalue%'),
            array(ChoiceType::TYPE_CONTAINS, array('somevalue', 'somevalue'), '%somevalue%'),
            array(ChoiceType::TYPE_EQUAL, array('somevalue'), '%somevalue%'),
            array(ChoiceType::TYPE_EQUAL, array('somevalue', 'somevalue'), '%somevalue%'),
        );
    }

    /**
     * @dataProvider getFiltersMultiple
     */
    public function testFilterMultipleSwitch($choiceType, $value, $expectedValue)
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            array('type' => $choiceType, 'value' => $value)
        );

        $this->qb->getNodeByPath('where[0].constraint[0].constraint[0].operand[0]');

        $this->assertTrue($this->filter->isActive());
    }
}
