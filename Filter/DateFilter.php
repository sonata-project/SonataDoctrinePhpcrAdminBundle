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
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

class DateFilter extends Filter
{
    /**
     * Applies a constraint to the query
     *
     * @param Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery $queryBuilder
     * @param string $alias has no effect
     * @param string $field field uhere to apply the constraint
     * @param array $data determines the constraint
     * @return
     */
    public function filter($queryBuilder, $alias = null, $field, $data)
    {
        if (!$data || !is_array($data) || !isset($data['value'])) {
            return;
        }

        if (isset($data['value']['year']) && $data['value']['year'] && isset($data['value']['month']) && $data['value']['month'] && isset($data['value']['day']) && $data['value']['day']) {

            $data['type'] = isset($data['type']) ? $data['type'] : DateType::TYPE_EQUAL;

            $qf = $queryBuilder->getQueryObjectModelFactory();

            $date = '' . $data['value']['year']. '-' . $data['value']['month'] . '-' . $data['value']['day'];

            $from = new \DateTime($date);
            $to = new \DateTime($date . ' +86399 seconds'); // 23 hours 59 minutes 59 seconds
            switch ($data['type']) {
                case DateType::TYPE_GREATER_EQUAL:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_GREATER_THAN_OR_EQUAL_TO, $qf->literal($from));
                    break;
                case DateType::TYPE_GREATER_THAN:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_GREATER_THAN, $qf->literal($to));
                    break;
                case DateType::TYPE_LESS_EQUAL:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_LESS_THAN_OR_EQUAL_TO, $qf->literal($to));
                    break;
                case DateType::TYPE_LESS_THAN:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_LESS_THAN, $qf->literal($from));
                    break;
                case DateType::TYPE_NULL:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_EQUAL_TO, $qf->literal(null));
                    break;
                case DateType::TYPE_NOT_NULL:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_NOT_EQUAL_TO, $qf->literal(null));
                    break;
                case DateType::TYPE_EQUAL:
                default:
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_LESS_THAN_OR_EQUAL_TO, $qf->literal($to));
                    $queryBuilder->andWhere($constraint);
                    $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_GREATER_THAN_OR_EQUAL_TO, $qf->literal($from));
            }
            $queryBuilder->andWhere($constraint);
        }
    }

    public function getDefaultOptions()
    {
        return array(
            'date_format' => 'yyyy-MM-dd',
        );
    }

    public function getRenderSettings()
    {
        return array('sonata_type_filter_date', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
