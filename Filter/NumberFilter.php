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

use Doctrine\ODM\PHPCR\Query\ExpressionBuilder;
use Doctrine\ODM\PHPCR\Query\Expression\Comparison;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class NumberFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $type = isset($data['type']) ? $data['type'] : false;
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
        return array('sonata_type_filter_number', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
