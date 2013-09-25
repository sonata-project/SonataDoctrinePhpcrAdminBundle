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

use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class StringFilter extends Filter
{
    /**
     * {@inheritDoc}
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
                $where->eq()->field('a.'.$field)->literal($data['value']);
                break;
            case ChoiceType::TYPE_NOT_CONTAINS:
                $where->fullTextSearch('a.'.$field, '* -'.$data['value']);
                break;
            case ChoiceType::TYPE_CONTAINS:
                $where->like()->field('a.'.$field)->literal('%'.$data['value'].'%');
                break;
            case ChoiceType::TYPE_CONTAINS_WORDS:
            default:
                $where->fullTextSearch('a.'.$field, $data['value']);
        }

        // filter is active as we have now modified the query
        $this->active = true;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'format'   => '%%%s%%'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings()
    {
        return array('doctrine_phpcr_type_filter_choice', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
