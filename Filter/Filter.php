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

abstract class Filter extends BaseFilter
{
    /**
     * @var bool $active
     */
    protected $active = false;

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
        $this->value = $value;
        $this->filter($queryBuilder, null, $this->getFieldName(), $value);
    }

    /**
     * @param $queryBuilder
     * @param $parameter
     */
    protected function applyWhere($queryBuilder, $parameter)
    {
        if ($this->getCondition() == self::CONDITION_OR) {
            $queryBuilder->orWhere($parameter);
        } else {
            $queryBuilder->andWhere($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }
}
