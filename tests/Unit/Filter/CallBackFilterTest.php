<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Tests\Unit\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrinePHPCRAdminBundle\Filter\CallbackFilter;

class CallBackFilterTest extends BaseTestCase
{
    public function testFilterNullData()
    {
        $filter = new CallbackFilter();
        $filter->initialize('field_name', ['callback' => function () {
        }]);
        $res = $filter->filter($this->proxyQuery, null, 'somefield', null);
        $this->assertNull($res);
        $this->assertFalse($filter->isActive());
    }

    public function testFilterEmptyArrayData()
    {
        $filter = new CallbackFilter();

        $filter->initialize('field_name', ['callback' => function () {
        }]);
        $res = $filter->filter($this->proxyQuery, null, 'somefield', []);
        $this->assertNull($res);
        $this->assertFalse($filter->isActive());
    }

    public function testFilterMethod()
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => [$this, 'callbackMethod'],
        ]);

        $filter->filter($this->proxyQuery, null, 'somefield', ['type' => '', 'value' => 'somevalue']);

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals('somevalue', $opStatic->getValue());

        $this->assertTrue($filter->isActive());
    }

    public function callbackMethod(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();
        $queryBuilder->andWhere()->eq()->field('a.'.$field)->literal($data['value']);

        return true;
    }

    public function testFilterClosure()
    {
        $this->proxyQuery->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => function (ProxyQueryInterface $proxyQuery, $alias, $field, $data) {
                $queryBuilder = $proxyQuery->getQueryBuilder();
                $queryBuilder->andWhere()->eq()->field('a.'.$field)->literal($data['value']);

                return true;
            },
        ]);

        $filter->filter($this->proxyQuery, null, 'somefield', ['type' => '', 'value' => 'somevalue']);

        $opDynamic = $this->qbTester->getNode('where.constraint.operand_dynamic');
        $opStatic = $this->qbTester->getNode('where.constraint.operand_static');

        $this->assertEquals('a', $opDynamic->getAlias());
        $this->assertEquals('somefield', $opDynamic->getField());
        $this->assertEquals('somevalue', $opStatic->getValue());

        $this->assertTrue($filter->isActive());
    }

    public function testWithoutCallback()
    {
        $this->expectException(\RuntimeException::class);

        $filter = new CallbackFilter();

        $filter->setOption('callback', null);
        $filter->filter($this->proxyQuery, null, 'somefield', null);
    }

    public function testCallbackNotCallable()
    {
        $this->expectException(\RuntimeException::class);

        $filter = new CallbackFilter();

        $filter->setOption('callback', 'someCallback');
        $filter->filter($this->proxyQuery, null, 'somefield', null);
    }
}
