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

use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\DateFilter;

class DateFilterTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new DateFilter();
    }

    // @todo: Can probably factor the following 4 test cases into a common class
    //        IF we introduce another test with the same need.

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

    public function getFilters()
    {
        return [
            ['gte', DateOperatorType::TYPE_GREATER_EQUAL],
            ['gt', DateOperatorType::TYPE_GREATER_THAN],
            ['lte', DateOperatorType::TYPE_LESS_EQUAL],
            ['lt', DateOperatorType::TYPE_LESS_THAN],
            ['eq', DateOperatorType::TYPE_NULL, null],
            ['neq', DateOperatorType::TYPE_NOT_NULL, null],
            // test DateOperatorType::TYPE_EQUAL separately, special case.
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = '__null__'): void
    {
        $value = new \DateTime('2013/01/16 00:00:00');

        if ('__null__' === $expectedValue) {
            $expectedValue = new \DateTime('2013/01/16 00:00:00');
        }

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $choiceType, 'value' => $value]
        );

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertTrue(
            $expectedValue instanceof \DateTimeInterface ?
            $expectedValue->getTimestamp() === $opStatic->getValue()->getTimestamp() :
            $expectedValue === $opStatic->getValue()
        );

        $this->assertTrue($this->filter->isActive());
    }

    public function testFilterEquals(): void
    {
        $from = new \DateTime('2013/01/16 00:00:00');
        $to = new \DateTime('2013/01/16 23:59:59');

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => DateOperatorType::TYPE_EQUAL, 'value' => $from]
        );

        // FROM
        $opDynamic = $this->qbTester->getNode(
            'where.constraint.constraint.operand_dynamic'
        );
        $opStatic = $this->qbTester->getNode(
            'where.constraint.constraint.operand_static'
        );

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertSame($from->getTimestamp(), $opStatic->getValue()->getTimestamp());

        // TO
        $opDynamic = $this->qbTester->getNode(
            'where.constraint.constraint[1].operand_dynamic'
        );
        $opStatic = $this->qbTester->getNode(
            'where.constraint.constraint[1].operand_static'
        );

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertSame($to->getTimestamp(), $opStatic->getValue()->getTimestamp());

        $this->assertTrue($this->filter->isActive());
    }
}
