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

class DateFilterTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->filter = new DateFilter;
    }

    // @todo: Can probably factor the following 4 test cases into a common class
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
            // test ChoiceTYPE::TYPE_EQUAL separately, special case.
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

        $this->filter->filter(
            $this->proxyQuery, 
            null, 
            'somefield', 
            array('type' => $choiceType, 'value' => $value)
        );

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals($expectedValue, $opStatic->getValue());

        $this->assertTrue($this->filter->isActive());
    }

    public function testFilterEquals()
    {
        $from = new \DateTime('2013/01/16 00:00:00');
        $to = new \DateTime('2013/01/16 23:59:59');

        $this->filter->filter(
            $this->proxyQuery, 
            null, 
            'somefield', 
            array('type' => DateType::TYPE_EQUAL, 'value' => $from)
        );

        // FROM
        $opDynamic = $this->qbTester->getNode(
            'where.constraint.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode(
            'where.constraint.constraint.operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals($from, $opStatic->getValue());

        // TO
        $opDynamic = $this->qbTester->getNode(
            'where.constraint.constraint[1].operand_dynamic');
        $opStatic = $this->qbTester->getNode(
            'where.constraint.constraint[1].operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals($to, $opStatic->getValue());

        $this->assertTrue($this->filter->isActive());
    }
}
