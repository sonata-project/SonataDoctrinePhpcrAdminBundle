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

use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\BooleanFilter;

class BooleanFilterTest extends BaseTestCase
{
    /**
     * @var BooleanFilter
     */
    private $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new BooleanFilter();
    }

    public function testFilterNullData(): void
    {
        $this->filter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayData(): void
    {
        $this->filter->filter($this->proxyQuery, null, 'somefield', []);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataSpecifiedType(): void
    {
        $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => BooleanType::TYPE_YES]);
        $this->assertFalse($this->filter->isActive());
    }

    public function testFilterEmptyArrayDataWithMeaninglessValue(): void
    {
        $this->filter->filter($this->proxyQuery, null, 'somefield', ['type' => BooleanType::TYPE_YES, 'value' => 'someValue']);
        $this->assertFalse($this->filter->isActive());
    }

    public function getFilters()
    {
        return [
            ['eq', BooleanType::TYPE_YES, 1],
            ['eq', BooleanType::TYPE_NO, 0],
        ];
    }

    /**
     * @dataProvider getFilters
     */
    public function testFilterSwitch($operatorMethod, $value, $expectedValue): void
    {
        $this->filter->filter(
            $this->proxyQuery,
            null,
            'somefield',
            ['type' => '', 'value' => $value]
        );

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals($expectedValue, $opStatic->getValue());

        $this->assertTrue($this->filter->isActive());
    }
}
