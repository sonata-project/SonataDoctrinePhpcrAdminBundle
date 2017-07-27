<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class NodeNameFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);
        $data['type'] = empty($data['type']) ? ChoiceType::TYPE_CONTAINS : $data['type'];

        if (strlen($data['value']) == 0) {
            return;
        }

        $where = $this->getWhere($proxyQuery);

        switch ($data['type']) {
            case ChoiceType::TYPE_EQUAL:
                $where->eq()->localName($alias)->literal($data['value']);
                break;
            case ChoiceType::TYPE_CONTAINS:
            default:
                $where->like()->localName($alias)->literal('%'.$data['value'].'%');
        }

        // filter is active as we have now modified the query
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'format' => '%%%s%%',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType', array(
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ));
    }
}
