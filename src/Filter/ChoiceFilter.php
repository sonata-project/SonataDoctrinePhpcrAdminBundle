<?php

declare(strict_types=1);

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
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;

class ChoiceFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !\is_array($data) || !\array_key_exists('type', $data) || !\array_key_exists('value', $data)) {
            return;
        }

        $values = (array) $data['value'];
        $type = $data['type'];

        // clean values
        foreach ($values as $key => $value) {
            $value = trim((string) $value);
            if (!$value) {
                unset($values[$key]);
            } else {
                $values[$key] = $value;
            }
        }

        // if values not set or "all" specified, do not do this filter
        if (!$values || \in_array('all', $values, true)) {
            return;
        }

        if (EqualOperatorType::TYPE_NOT_EQUAL === $type) {
            $where = $this->getWhere($proxyQuery)->andX();
        } else {
            $where = $this->getWhere($proxyQuery)->orX();
        }

        foreach ($values as $value) {
            if (EqualOperatorType::TYPE_NOT_EQUAL === $type) {
                $where->neq()->field('a.'.$field)->literal($value);
            } else {
                $where->eq()->field('a.'.$field)->literal($value);
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
        return [
            'operator_type' => EqualOperatorType::class,
            'operator_options' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return [DefaultType::class, [
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
