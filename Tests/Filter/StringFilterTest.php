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

class StringFilterTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->filter = new StringFilter();
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

    public function testFilterEmptyArrayDataWithMeaninglessValue()
    {
        $this->proxyQuery->expects($this->never())
            ->method('getQueryBuilder');

        $this->filter->filter($this->proxyQuery, null, 'somefield', array('type' => ChoiceType::TYPE_EQUAL, 'value' => ' '));
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return array(
            array(ChoiceType::TYPE_EQUAL, array(
                'where.constraint.operand_dynamic' => array(
                    'getAlias' => 'a',
                    'getField' => 'somefield',
                ),
                'where.constraint.operand_static' => array(
                    'getValue' => 'somevalue',
                ),
            )),
            array(ChoiceType::TYPE_NOT_CONTAINS, array(
                'where.constraint' => array(
                    'getField' => 'somefield',
                    'getFullTextSearchExpression' => '* -somevalue'),
            )),
            array(ChoiceType::TYPE_CONTAINS, array(
                'where.constraint.operand_dynamic' => array(
                    'getAlias' => 'a',
                    'getField' => 'somefield',
                ),
                'where.constraint.operand_static' => array(
                    'getValue' => '%somevalue%',
                ),
            )),
            array(ChoiceType::TYPE_CONTAINS_WORDS, array(
                'where.constraint' => array(
                    'getField' => 'somefield',
                    'getFullTextSearchExpression' => 'somevalue'),
            )),
        );
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($choiceType, $assertPaths)
    {
        $this->filter->filter(
            $this->proxyQuery, 
            null, 
            'somefield', 
            array('type' => $choiceType, 'value' => 'somevalue')
        );
        $this->assertTrue($this->filter->isActive());

        foreach ($assertPaths as $path => $assertions) {
            $node = $this->qbTester->getNode($path);
            foreach ($assertions as $methodName => $expectedValue) {
                $res = $node->$methodName();
                $this->assertEquals($expectedValue, $res);
            }
        }

        $this->assertTrue($this->filter->isActive());
    }
}
