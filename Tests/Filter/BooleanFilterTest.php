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

use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\BooleanFilter;

class BooleanFilterTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->filter = new BooleanFilter();
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
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => BooleanType::TYPE_YES));
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue()
    {
        $this->proxyQuery->expects($this->never())
            ->method('andWhere');

        $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => BooleanType::TYPE_YES, 'value' => 'someValue'));
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return array(
            array('eq', BooleanType::TYPE_YES, 1),
            array('eq', BooleanType::TYPE_NO, 0),
        );
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $value, $expectedValue)
    {
        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            array('type' => '', 'value' => $value)
        );

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals($expectedValue, $opStatic->getValue());

        $this->assertTrue($this->filter->isActive());
    }
}
