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

use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class DateFilter extends Filter
{
    /**
     * Applies a constraint to the query
     *
     * @param ProxyQueryInterface $proxyQuery
     * @param string $alias has no effect
     * @param string $field field where to apply the constraint
     * @param array $data determines the date constraint [value => [year => Y, month => m, day => d], type => DateType::TYPE_GREATER_EQUAL|DateType::TYPE_GREATER_THAN|DateType::TYPE_LESS_EQUAL|DateType::TYPE_LESS_THAN|DateType::TYPE_NULL|DateType::TYPE_NOT_NULL|DateType::TYPE_EQUAL]
     * @return
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !isset($data['value'])) {
            return;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : DateType::TYPE_EQUAL;

        $qb = $proxyQuery->getQueryBuilder();
        $eb = $qb->expr();

        $from = $data['value'];
        $to = new \DateTime($from->format('Y-m-d') . ' +86399 seconds'); // 23 hours 59 minutes 59 seconds

        switch ($data['type']) {
            case DateType::TYPE_GREATER_EQUAL:
                $expr = $eb->gte($field, $from);
                break;
            case DateType::TYPE_GREATER_THAN:
                $expr = $eb->gt($field, $from);
                break;
            case DateType::TYPE_LESS_EQUAL:
                $expr = $eb->lte($field, $from);
                break;
            case DateType::TYPE_LESS_THAN:
                $expr = $eb->lt($field, $from);
                break;
            case DateType::TYPE_NULL:
                $expr = $eb->eq($field, null);
                break;
            case DateType::TYPE_NOT_NULL:
                $expr = $eb->neq($field, null);
                break;
            case DateType::TYPE_EQUAL:
            default:
                $expr = $eb->andX(
                    $expr = $eb->gte($field, $from),
                    $expr = $eb->lte($field, $to)
                );
        }

        $this->applyWhere($qb, $expr);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'date_format' => 'yyyy-MM-dd',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('sonata_type_filter_date', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
