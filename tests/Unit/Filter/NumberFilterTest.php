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

use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\NumberFilter;

class NumberFilterTest extends BaseTestCase
{
    /**
     * @var NumberFilter
     */
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new NumberFilter();
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
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => NumberType::TYPE_EQUAL]);
        $this->assertNull($res);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue(): void
    {
        $this->proxyQuery->expects($this->never())
            ->method('getQueryBuilder');

        $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => NumberType::TYPE_EQUAL, 'value' => ' ']);
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return [
            ['jcr.operator.greater.than.or.equal.to', NumberOperatorType::TYPE_GREATER_EQUAL, 2],
            ['jcr.operator.greater.than', NumberOperatorType::TYPE_GREATER_THAN, 3],
            ['jcr.operator.less.than.or.equal.to', NumberOperatorType::TYPE_LESS_EQUAL, 4],
            ['jcr.operator.less.than', NumberOperatorType::TYPE_LESS_THAN, 5],
            ['jcr.operator.equal.to', NumberOperatorType::TYPE_EQUAL, 6],
            ['jcr.operator.equal.to', 'default', 7],
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operator, $choiceType, $expectedValue): void
    {
        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $choiceType, 'value' => $expectedValue]
        );

        $op = $this->qbTester->getNode('where.constraint');
        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertSame($operator, $op->getOperator());
        $this->assertSame($expectedValue, $opStatic->getValue());

        $this->assertTrue($this->filter->isActive());
    }
}
