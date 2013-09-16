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

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class ChoiceFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        if (is_array($data['value'])) {
            if (count($data['value']) == 0) {
                return;
            }

            if (in_array('all', $data['value'], true)) {
                return;
            }

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                if (count($data['value']) > 1) {
                    $values = $data['value'];
                    $where = $this->getWhere();
                    $where->andX()
                        ->fullTextSearch('a.'.$field, '*-'.array_pop($values))
                        ->fullTextSearch('a.'.$field, '*-'.array_pop($values));

                    foreach ($values as $value) {
                        $and = $where->getChild(QBConstants::NT_CONSTRAINT);
                        $where->removeChildrenOfType(QBConstants::NT_CONSTRAINT);
                        $andX = $where->andX()->fullTextSearch('a.'.$field, '*-'.$value);
                        $andX->addChild($and);
                    }
                } else {
                    $where->fullTextSearch('a.'.$field, '*-'.$data['value']);
                }
            } else {
                // contains
                if (count($data['value']) > 1) {
                    $values = $data['value'];
                    $where = $this->getWhere();
                    $where->andX()
                        ->like('a.'.$field)->literal('%'.array_pop($values).'%')
                        ->like('a.'.$field)->literal('%'.array_pop($values).'%');

                    foreach ($values as $value) {
                        $and = $where->getChild(QBConstants::NT_CONSTRAINT);
                        $where->removeChildrenOfType(QBConstants::NT_CONSTRAINT);
                        $andX = $where->andX()->like('a.'.$field)->literal('%'.$value.'%');
                        $andX->addChild($and);
                    }
                } else {
                    $where->like('a.'.$field, '%'.$data['value'].'%');
                }
            }

        } else {
            $data['value'] = trim($data['value']);

            if (strlen($data['value']) == 0 || $data['value'] === 'all') {
                return;
            }

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                $this->getWhere()->fullTextSearch('a.'.$field, $data['value']);
            } elseif ($data['type'] == ChoiceType::TYPE_CONTAINS) {
                $this->getWhere()->like('a.'.$field)->litreal('%'.$data['value'].'%');
            } else {
                $this->getWhere()->like('a.'.$field)->literal('value');
            }

        }

        // filter is active as we have now modified the query
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
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
