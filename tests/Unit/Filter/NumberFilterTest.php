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
use Sonata\DoctrinePHPCRAdminBundle\Filter\NumberFilter;

class NumberFilterTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new NumberFilter();
    }

    public function testFilterNullData(): void
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', null);
        static::assertNull($res);
        static::assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData(): void
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', []);
        static::assertNull($res);
        static::assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType(): void
    {
        $res = $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => NumberType::TYPE_EQUAL]);
        static::assertNull($res);
        static::assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue(): void
    {
        $this->proxyQuery->expects(static::never())
            ->method('getQueryBuilder');

        $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => NumberType::TYPE_EQUAL, 'value' => ' ']);
        static::assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return [
            ['gte', NumberType::TYPE_GREATER_EQUAL, 2],
            ['gt', NumberType::TYPE_GREATER_THAN, 3],
            ['lte', NumberType::TYPE_LESS_EQUAL, 4],
            ['lt', NumberType::TYPE_LESS_THAN, 5],
            ['eq', NumberType::TYPE_EQUAL, 6],
            ['eq', 'default', 7],
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = 'somevalue'): void
    {
        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $choiceType, 'value' => $expectedValue]
        );

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        static::assertSame('a', $opDynamic->getAlias());
        static::assertSame('somefield', $opDynamic->getField());
        static::assertSame($expectedValue, $opStatic->getValue());

        static::assertTrue($this->filter->isActive());
    }
}
