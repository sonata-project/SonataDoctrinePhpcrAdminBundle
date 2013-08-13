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
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class NumberFilter extends Filter
{
    /**
     * @param ProxyQueryInterface $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $data
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $queryBuilder = $proxyQuery->getQueryBuilder();

        $type = isset($data['type']) ? $data['type'] : false;

        $this->applyWhere($queryBuilder, $this->getExpression($queryBuilder->expr(), $type, $field, $data['value']));
    }

    /**
     * @param ExpressionBuilder $eb
     * @param string $type
     * @param string $field
     * @param string $value
     *
     * @return Comparison
     */
    private function getExpression(ExpressionBuilder $eb, $type, $field, $value)
    {
        switch ($type) {
            case NumberType::TYPE_GREATER_EQUAL:
                $expr = $eb->gte($field, $value);
                break;
            case NumberType::TYPE_GREATER_THAN:
                $expr = $eb->gt($field, $value);
                break;
            case NumberType::TYPE_LESS_EQUAL:
                $expr = $eb->lte($field, $value);
                break;
            case NumberType::TYPE_LESS_THAN:
                $expr = $eb->lt($field, $value);
                break;
            case NumberType::TYPE_EQUAL:
            default:
                $expr = $eb->eq($field, $value);
        }

        return $expr;
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
        return array('sonata_type_filter_number', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
