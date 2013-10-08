<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Filter;

use Sonata\AdminBundle\Filter\Filter as BaseFilter;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;

abstract class Filter extends BaseFilter
{
    /**
     * @var boolean
     */
    protected $active = false;

    /**
     * @param ProxyQuery $queryBuilder
     * @param mixed      $value
     */
    public function apply($queryBuilder, $value)
    {
        $this->value = $value;
        $this->filter($queryBuilder, $queryBuilder->getAlias(), $this->getFieldName(), $value);
    }

    /**
     * Add the where statement for this filter to the query.
     *
     * @param ProxyQuery $proxy
     */
    protected function getWhere(ProxyQuery $proxy)
    {
        $queryBuilder = $proxy->getQueryBuilder();
        if ($this->getCondition() == self::CONDITION_OR) {
            return $queryBuilder->orWhere();
        }

        return $queryBuilder->andWhere();
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        return $this->active;
    }
}
