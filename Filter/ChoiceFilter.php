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

        if (is_array($data['value'])) {
            if (count($data['value']) == 0) {
                return;
            }

            if (in_array('all', $data['value'], true)) {
                return;
            }

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                if (count($data['value']) > 1) {
                    $constraints = array();
                    foreach ($data['value'] as $value) {
                        $constraints[] = $queryBuilder->expr()->textSearch($field, '* -'.$value);
                    }

                    $this->applyWhere($queryBuilder, call_user_func_array(array($queryBuilder->expr(), 'andX'), $constraints));
                } else {
                    $this->applyWhere($queryBuilder, $queryBuilder->expr()->textSearch($field, '* -'.$data['value'][0]));
                }
            } else {
                // contains
                if (count($data['value']) > 1) {
                    $constraints = array();
                    foreach ($data['value'] as $value) {
                        $constraints[] = $queryBuilder->expr()->like($field, '%'.$value.'%');
                    }

                    $this->applyWhere($queryBuilder, call_user_func_array(array($queryBuilder->expr(), 'orX'), $constraints));
                } else {
                    $this->applyWhere($queryBuilder, $queryBuilder->expr()->like($field, '%'.$data['value'][0].'%'));
                }
            }

        } else {
            $data['value'] = trim($data['value']);

            if (strlen($data['value']) == 0 || $data['value'] === 'all') {
                return;
            }

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                $this->applyWhere($queryBuilder, $queryBuilder->expr()->textSearch($field, '* -'.$data['value']));
            } elseif ($data['type'] == ChoiceType::TYPE_CONTAINS) {
                $this->applyWhere($queryBuilder, $queryBuilder->expr()->like($field, '%'.$data['value'].'%'));
            } else {
                $this->applyWhere($queryBuilder, $queryBuilder->expr()->eq($field, $data['value']));
            }
        }
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
            'operator_type' => 'sonata_type_equal',
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
