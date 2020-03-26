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
    /**
     * @var DateFilter
     */
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new DateFilter();
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

    public function getFilters()
    {
        return [
            ['jcr.operator.greater.than.or.equal.to', DateOperatorType::TYPE_GREATER_EQUAL, new \DateTime('2013/01/16 00:00:00')],
            ['jcr.operator.greater.than', DateOperatorType::TYPE_GREATER_THAN, new \DateTime('2013/01/16 00:00:00')],
            ['jcr.operator.less.than.or.equal.to', DateOperatorType::TYPE_LESS_EQUAL, new \DateTime('2013/01/16 00:00:00')],
            ['jcr.operator.less.than', DateOperatorType::TYPE_LESS_THAN, new \DateTime('2013/01/16 00:00:00')],
            ['jcr.operator.equal.to', DateOperatorType::TYPE_NULL, null],
            ['jcr.operator.not.equal.to', DateOperatorType::TYPE_NOT_NULL, null],
            // test DateOperatorType::TYPE_EQUAL separately, special case.
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch(string $operator, int $choiceType, ?\DateTime $expectedValue): void
    {
        $value = new \DateTime('2013/01/16 00:00:00');

        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => $choiceType, 'value' => $value]
        );

        $op = $this->qbTester->getNode('where.constraint');
        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertSame($operator, $op->getOperator());
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
        $op = $this->qbTester->getNode('where.constraint.constraint');
        $opDynamic = $this->qbTester->getNode('where.constraint.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.constraint.operand_static');

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertSame('jcr.operator.greater.than.or.equal.to', $op->getOperator());
        $this->assertSame($from->getTimestamp(), $opStatic->getValue()->getTimestamp());

        // TO
        $op = $this->qbTester->getNode('where.constraint.constraint[1]');
        $opDynamic = $this->qbTester->getNode('where.constraint.constraint[1].operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.constraint[1].operand_static');

        $this->assertSame('a', $opDynamic->getAlias());
        $this->assertSame('somefield', $opDynamic->getField());
        $this->assertSame('jcr.operator.less.than.or.equal.to', $op->getOperator());
        $this->assertSame($to->getTimestamp(), $opStatic->getValue()->getTimestamp());

        $this->assertTrue($this->filter->isActive());
    }
}
