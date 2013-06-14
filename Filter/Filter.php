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
    protected $active = false;

    public function apply($queryBuilder, $value)
    {
        $this->value = $value;

        $this->filter($queryBuilder, null, $this->getFieldName(), $value);
    }

    protected function applyWhere($queryBuilder, $parameter)
    {
        $queryBuilder->andWhere($parameter);


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
