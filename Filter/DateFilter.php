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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class DateFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !isset($data['value'])) {
            return;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : DateType::TYPE_EQUAL;

        $where = $this->getWhere($proxyQuery);

        $from = $data['value'];
        $to = new \DateTime($from->format('Y-m-d') . ' +86399 seconds'); // 23 hours 59 minutes 59 seconds

        switch ($data['type']) {
            case DateType::TYPE_GREATER_EQUAL:
                $where->gte()->field('a.'.$field)->literal($from);
                break;
            case DateType::TYPE_GREATER_THAN:
                $where->gt()->field('a.'.$field)->literal($from);
                break;
            case DateType::TYPE_LESS_EQUAL:
                $where->lte()->field('a.'.$field)->literal($from);
                break;
            case DateType::TYPE_LESS_THAN:
                $where->lt()->field('a.'.$field)->literal($from);
                break;
            case DateType::TYPE_NULL:
                $where->eq()->field('a.'.$field)->literal(null);
                break;
            case DateType::TYPE_NOT_NULL:
                $where->neq()->field('a.'.$field)->literal(null);
                break;
            case DateType::TYPE_EQUAL:
            default:
                $where->andX()
                    ->gte()->field('a.'.$field)->literal($from)->end()
                    ->lte()->field('a.'.$field)->literal($to)->end();
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
            'date_format' => 'yyyy-MM-dd',
        );
    }

    /**
     * {@inheritDoc}
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
