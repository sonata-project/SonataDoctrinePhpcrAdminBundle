<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Filter;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\ChoiceFilter;

class ChoiceFilterTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new ChoiceFilter();
    }

    public function testFilterNullData(): void
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData(): void
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', []);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType(): void
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => ChoiceType::TYPE_EQUAL]);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function getMeaninglessValues()
    {
        return [
            ['  '],
            [null],
            [false],
            ['all'],
            [[]],
            [['', 'all']],
        ];
    }

    /**
     * @dataProvider getMeaninglessValues
     */
    public function testFilterEmptyArrayDataWithMeaninglessValue($value): void
    {
        $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => ChoiceType::TYPE_EQUAL, 'value' => $value]);
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return [
            ['eq', ChoiceType::TYPE_EQUAL],
            ['textSearch', ChoiceType::TYPE_NOT_CONTAINS, '* -somevalue'],
            ['like', ChoiceType::TYPE_CONTAINS, '%somevalue%'],
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = 'somevalue'): void
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $choiceType, 'value' => 'somevalue']
        );
        $this->assertTrue($this->filter->isActive());
    }

    public function getFiltersMultiple()
    {
        return [
            [[
                'choiceType' => ChoiceType::TYPE_NOT_CONTAINS,
                'value' => 'somevalue',
                'qbNodeCount' => 6,
                'assertPaths' => [
                    'where.constraint.constraint[0].constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint[0].constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_NOT_CONTAINS,
                'value' => ['somevalue', 'somevalue'],
                'qbNodeCount' => 10,
                'assertPaths' => [
                    'where.constraint.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                    'where.constraint.constraint[1].constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint[1].constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_CONTAINS,
                'value' => 'somevalue',
                'qbNodeCount' => 5,
                'assertPaths' => [
                    'where.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_CONTAINS,
                'value' => ['somevalue', 'somevalue'],
                'qbNodeCount' => 8,
                'assertPaths' => [
                    'where.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                    'where.constraint.constraint[1].operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint[1].operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_CONTAINS,
                'value' => 'somevalue',
                'qbNodeCount' => 5,
                'assertPaths' => [
                    'where.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_CONTAINS,
                'value' => ['somevalue', 'somevalue'],
                'qbNodeCount' => 8,
                'assertPaths' => [
                    'where.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                    'where.constraint.constraint[1].operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint[1].operand_static' => [
                        'getValue' => '%somevalue%',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_EQUAL,
                'value' => 'somevalue',
                'qbNodeCount' => 5,
                'assertPaths' => [
                    'where.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => 'somevalue',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => 'somevalue',
                    ],
                ],
            ]],
            [[
                'choiceType' => ChoiceType::TYPE_EQUAL,
                'value' => ['somevalue', 'somevalue'],
                'qbNodeCount' => 8,
                'assertPaths' => [
                    'where.constraint.constraint.operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint.operand_static' => [
                        'getValue' => 'somevalue',
                    ],
                    'where.constraint.constraint[1].operand_dynamic' => [
                        'getAlias' => 'a',
                        'getField' => 'somefield',
                    ],
                    'where.constraint.constraint[1].operand_static' => [
                        'getValue' => 'somevalue',
                    ],
                ],
            ]],
        ];
    }

    /**
     * @dataProvider getFiltersMultiple
     */
    public function testFilterMultipleSwitch($options): void
    {
        $options = array_merge([
            'choiceType' => null,
            'value' => null,
            'assertPaths' => [],
            'qbNodeCount' => 0,
        ], $options);

        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $options['choiceType'], 'value' => $options['value']]
        );

        foreach ($options['assertPaths'] as $path => $methodAssertions) {
            $node = $this->qbTester->getNode($path);
            foreach ($methodAssertions as $methodName => $expectedValue) {
                $res = $node->$methodName();
                $this->assertEquals($expectedValue, $res);
            }
        }

        $this->assertTrue($this->filter->isActive());
        $this->assertEquals($options['qbNodeCount'], \count($this->qbTester->getAllNodes()));
    }
}
