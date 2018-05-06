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

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Tools\Test\QueryBuilderTester;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;

class BaseTestCase extends TestCase
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var QueryBuilderTester
     */
    protected $qbTester;

    /**
     * @var ProxyQuery
     */
    protected $proxyQuery;

    public function setUp(): void
    {
        $this->qb = new QueryBuilder();
        $this->qbTester = new QueryBuilderTester($this->qb);

        $this->proxyQuery = $this->getMockBuilder(ProxyQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->proxyQuery->expects($this->any())
            ->method('getQueryBuilder')
            ->will($this->returnValue($this->qb));
    }
}
