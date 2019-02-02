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
use Sonata\AdminBundle\Form\Type\Filter\NumberType;

class NumberFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data): void
    {
        if (!$data || !\is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $type = $data['type'] ?? false;
        $where = $this->getWhere($proxyQuery);

        $value = $data['value'];

        switch ($type) {
            case NumberType::TYPE_GREATER_EQUAL:
                $where->gte()->field('a.'.$field)->literal($value);

                break;
            case NumberType::TYPE_GREATER_THAN:
                $where->gt()->field('a.'.$field)->literal($value);

                break;
            case NumberType::TYPE_LESS_EQUAL:
                $where->lte()->field('a.'.$field)->literal($value);

                break;
            case NumberType::TYPE_LESS_THAN:
                $where->lt()->field('a.'.$field)->literal($value);

                break;
            case NumberType::TYPE_EQUAL:
            default:
                $where->eq()->field('a.'.$field)->literal($value);
        }

        // filter is active as we have now modified the query
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return ['sonata_type_filter_number', [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
