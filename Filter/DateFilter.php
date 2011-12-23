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
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        //$data['value'] = trim($data['value']);
        $data['type'] = !isset($data['type']) ?  ChoiceType::TYPE_CONTAINS : $data['type'];

        //if (strlen($data['value']) == 0) {
        //    return;
        //}

        $qf = $queryBuilder->getQueryObjectModelFactory();
        
        $datetime = new \DateTime($data['value']['year'].'-'.$data['value']['month'].'-'.$data['value']['day']);
        switch ($data['type']) {
        default:
            $value = \PHPCR\PropertyType::convertType($datetime, \PHPCR\PropertyType::STRING);
            $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_GREATER_THAN_OR_EQUAL_TO, $qf->literal('(CAST(\''.$value.'\' AS DATE))'));
            $queryBuilder->andWhere($constraint);
            $datetime->modify('+86399 seconds'); // 23 hours 59 minutes 59 seconds
            $value = \PHPCR\PropertyType::convertType($datetime, \PHPCR\PropertyType::STRING);
            $constraint = $qf->comparison($qf->propertyValue($field), Constants::JCR_OPERATOR_LESS_THAN_OR_EQUAL_TO, $qf->literal('(CAST(\''.$value.'\' AS DATE))'));
        }
        $queryBuilder->andWhere($constraint);
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
