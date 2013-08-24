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

use Sonata\AdminBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrinePHPCRAdminBundle\Filter\Filter as BaseFilter;

class BooleanFilter extends BaseFilter
{
    /**
     * @param ProxyQueryInterface $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $data
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        $queryBuilder = $proxyQuery->getQueryBuilder();

        if (is_array($data['value']) || !in_array($data['value'], array(BooleanType::TYPE_NO, BooleanType::TYPE_YES))) {
            return;
        }

        $expr = $queryBuilder->expr()->eq($field, ($data['value'] == BooleanType::TYPE_YES) ? 1 : 0);
        $this->applyWhere($queryBuilder, $expr);
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => 'hidden',
            'operator_options' => array(),
            'label'         => $this->getLabel()
        ));
    }
}
