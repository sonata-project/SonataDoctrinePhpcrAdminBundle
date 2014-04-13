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

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class ChoiceFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        $values = (array) $data['value'];
        $type = $data['type'];

        // clean values
        foreach ($values as $key => $value) {
            $value = trim($value);
            if (!$value) {
                unset($values[$key]);
            } else {
                $values[$key] = $value;
            }
        }

        // if values not set or "all" specified, do not do this filter
        if (!$values || in_array('all', $values, true)) {
            return;
        }

        $andX = $this->getWhere($proxyQuery)->andX();

        foreach ($values as $value) {
            if ($type == ChoiceType::TYPE_NOT_CONTAINS) {
                $andX->not()->like()->field('a.'.$field)->literal('%'.$value.'%');
            } elseif ($type == ChoiceType::TYPE_CONTAINS) {
                $andX->like()->field('a.'.$field)->literal('%'.$value.'%');
            } elseif ($type == ChoiceType::TYPE_EQUAL) {
                $andX->like()->field('a.'.$field)->literal($value);
            }
        }

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
            'operator_type' => 'sonata_type_equal',
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
