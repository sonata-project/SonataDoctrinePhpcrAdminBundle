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

use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrinePHPCRAdminBundle\Filter\Filter as BaseFilter;

class BooleanFilter extends BaseFilter
{
    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        if (is_array($data['value']) || !in_array($data['value'], array(BooleanType::TYPE_NO, BooleanType::TYPE_YES))) {
            return;
        }

        $where = $this->getWhere($proxyQuery);
        $where->eq()->field('a.'.$field)->literal($data['value'] == BooleanType::TYPE_YES ? 1 : 0);

        // filter is active as we have now modified the query
        $this->active = true;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritDoc}
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
