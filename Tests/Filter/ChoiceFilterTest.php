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
            array(array(
                'choiceType' => ChoiceType::TYPE_NOT_CONTAINS, 
                'value' => 'somevalue',
                'qbNodeCount' => 6,
                'assertPaths' => array(
                    'where.constraint.constraint[0].constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint[0].constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_NOT_CONTAINS, 
                'value' => array('somevalue', 'somevalue'),
                'qbNodeCount' => 10,
                'assertPaths' => array(
                    'where.constraint.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                    'where.constraint.constraint[1].constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint[1].constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_CONTAINS, 
                'value' => 'somevalue',
                'qbNodeCount' => 5,
                'assertPaths' => array(
                    'where.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_CONTAINS, 
                'value' => array('somevalue', 'somevalue'),
                'qbNodeCount' => 8,
                'assertPaths' => array(
                    'where.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                    'where.constraint.constraint[1].operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint[1].operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_CONTAINS, 
                'value' => 'somevalue',
                'qbNodeCount' => 5,
                'assertPaths' => array(
                    'where.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_CONTAINS, 
                'value' => array('somevalue', 'somevalue'),
                'qbNodeCount' => 8,
                'assertPaths' => array(
                    'where.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                    'where.constraint.constraint[1].operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint[1].operand_static' => array(
                        'getValue' => '%somevalue%',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_EQUAL, 
                'value' => 'somevalue',
                'qbNodeCount' => 5,
                'assertPaths' => array(
                    'where.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => 'somevalue',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => 'somevalue',
                    ),
                ),
            )),
            array(array(
                'choiceType' => ChoiceType::TYPE_EQUAL, 
                'value' => array('somevalue', 'somevalue'),
                'qbNodeCount' => 8,
                'assertPaths' => array(
                    'where.constraint.constraint.operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint.operand_static' => array(
                        'getValue' => 'somevalue',
                    ),
                    'where.constraint.constraint[1].operand_dynamic' => array(
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ),
                    'where.constraint.constraint[1].operand_static' => array(
                        'getValue' => 'somevalue',
                    ),
                ),
            )),
        );
    }

    /**
     * @dataProvider getFiltersMultiple
     */
    public function testFilterMultipleSwitch($options)
    {
        $options = array_merge(array(
            'choiceType' => null,
            'value' => null,
            'assertPaths' => array(),
            'qbNodeCount' => 0,
        ), $options);

        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            array('type' => $options['choiceType'], 'value' => $options['value'])
        );

        foreach ($options['assertPaths'] as $path => $methodAssertions) {
            $node = $this->qbTester->getNode($path);
            foreach ($methodAssertions as $methodName => $expectedValue) {
                $res = $node->$methodName();
                $this->assertEquals($expectedValue, $res);
            }
        }

        $this->assertTrue($this->filter->isActive());
        $this->assertEquals($options['qbNodeCount'], count($this->qbTester->getAllNodes()));
    }
}
