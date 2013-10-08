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

use Sonata\DoctrinePHPCRAdminBundle\Filter\NodeNameFilter;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class NodeNameFilterTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->filter = new NodeNameFilter();
    }

    public function getChoiceTypeForEmptyTests()
    {
        return ChoiceType::TYPE_EQUAL;
    }

    public function testFilterNullData()
    {
        $res = $this->filter->filter($this->proxyQuery, 'a', 'somefield', null);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData()
    {
        $res = $this->filter->filter($this->proxyQuery, 'a', 'somefield', array());
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType()
    {
        $res = $this->filter->filter($this->proxyQuery, 'a', 'somefield', array('type' => ChoiceType::TYPE_EQUAL));
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue()
    {
        $this->proxyQuery->expects($this->never())
            ->method('andWhere');

        $this->filter->filter($this->proxyQuery, 'a', 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => ' '));
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return array(
            array('eqNodeName', ChoiceType::TYPE_EQUAL),
            array('likeNodeName', ChoiceType::TYPE_NOT_CONTAINS, '%somevalue%'),
            array('likeNodeName', ChoiceType::TYPE_CONTAINS, '%somevalue%'),
            array('likeNodeName', ChoiceType::TYPE_CONTAINS_WORDS, '%somevalue%'),
        );
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = 'somevalue')
    {
        $this->proxyQuery->expects($this->exactly(1))
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->filter->filter(
            $this->proxyQuery,
            'a',
            'somefield',
            array('type' => $choiceType, 'value' => 'somevalue')
        );

        $localName = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $literal = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertEquals('a', $localName->getAlias());
        $this->assertEquals($expectedValue, $literal->getValue());

        $this->assertTrue($this->filter->isActive());
    }
}
