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

use Sonata\DoctrinePHPCRAdminBundle\Filter\StringFilter;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class StringFilterTest extends BaseTestCase
{
    /**
     * @var StringFilter
     */
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new StringFilter();
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

    public function testFilterEmptyArrayDataWithMeaninglessValue(): void
    {
        $this->proxyQuery->expects($this->never())
            ->method('getQueryBuilder');

        $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => ChoiceType::TYPE_EQUAL, 'value' => ' ']);
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return [
            [ChoiceType::TYPE_EQUAL, [
                'where.constraint.operand_dynamic' => [
                    'getAlias' => 'a',
                    'getField' => 'somefield',
                ],
                'where.constraint.operand_static' => [
                    'getValue' => 'somevalue',
                ],
            ]],
            [ChoiceType::TYPE_NOT_CONTAINS, [
                'where.constraint' => [
                    'getField' => 'somefield',
                    'getFullTextSearchExpression' => '* -somevalue', ],
            ]],
            [ChoiceType::TYPE_CONTAINS, [
                'where.constraint.operand_dynamic' => [
                    'getAlias' => 'a',
                    'getField' => 'somefield',
                ],
                'where.constraint.operand_static' => [
                    'getValue' => '%somevalue%',
                ],
            ]],
            [ChoiceType::TYPE_CONTAINS_WORDS, [
                'where.constraint' => [
                    'getField' => 'somefield',
                    'getFullTextSearchExpression' => 'somevalue', ],
            ]],
            'equalCaseInsensitiveComparision' => [ChoiceType::TYPE_EQUAL, [
                'where.constraint.operand_dynamic.operand_dynamic' => [
                    'getAlias' => 'a',
                    'getField' => 'somefield',
                ],
                'where.constraint.operand_static' => [
                    'getValue' => 'somevalue',
                ],
            ], true],
            'containsCaseInsensitiveComparision' => [ChoiceType::TYPE_CONTAINS, [
                'where.constraint.operand_dynamic.operand_dynamic' => [
                    'getAlias' => 'a',
                    'getField' => 'somefield',
                ],
                'where.constraint.operand_static' => [
                    'getValue' => '%somevalue%',
                ],
            ], true],
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($choiceType, $assertPaths, $isLowerCase = false): void
    {
        if ($isLowerCase) {
            $this->filter->setOption('compare_case_insensitive', true);
        }
        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $choiceType, 'value' => 'somevalue']
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
