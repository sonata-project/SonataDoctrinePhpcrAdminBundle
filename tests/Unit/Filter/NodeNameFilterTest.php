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

use Sonata\DoctrinePHPCRAdminBundle\Filter\NodeNameFilter;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class NodeNameFilterTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new NodeNameFilter();
    }

    public function getChoiceTypeForEmptyTests()
    {
        return ChoiceType::TYPE_EQUAL;
    }

    public function testFilterNullData(): void
    {
        $res = $this->filter->filter($this->proxyQuery, 'a', 'somefield', null);
        static::assertNull($res);
        static::assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData(): void
    {
        $res = $this->filter->filter($this->proxyQuery, 'a', 'somefield', []);
        static::assertNull($res);
        static::assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType(): void
    {
        $res = $this->filter->filter($this->proxyQuery, 'a', 'somefield', ['type' => ChoiceType::TYPE_EQUAL]);
        static::assertNull($res);
        static::assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue(): void
    {
        $this->filter->filter($this->proxyQuery, 'a', 'somefield', ['type' => ChoiceType::TYPE_EQUAL, 'value' => ' ']);
        static::assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return [
            ['eqNodeName', ChoiceType::TYPE_EQUAL],
            ['likeNodeName', ChoiceType::TYPE_NOT_CONTAINS, '%somevalue%'],
            ['likeNodeName', ChoiceType::TYPE_CONTAINS, '%somevalue%'],
            ['likeNodeName', ChoiceType::TYPE_CONTAINS_WORDS, '%somevalue%'],
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $choiceType, $expectedValue = 'somevalue'): void
    {
        $this->proxyQuery->expects(static::exactly(1))
            ->method('getQueryBuilder')
            ->willReturn($this->qb);

        $this->filter->filter(
            $this->proxyQuery,
            'a',
            'somefield',
            ['type' => $choiceType, 'value' => 'somevalue']
        );

        $localName = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $literal = $this->qbTester->getNode('where.constraint.operand_static');

        static::assertSame('a', $localName->getAlias());
        static::assertSame($expectedValue, $literal->getValue());

        static::assertTrue($this->filter->isActive());
    }
}
