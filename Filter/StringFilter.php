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

use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class StringFilter extends Filter
{
    /**
     * Applies a constraint to the query
     *
     * @param ProxyQueryInterface $proxyQuery
     * @param string $alias has no effect
     * @param string $field field uhere to apply the constraint
     * @param array $data determines the constraint
     * @return
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);
        $data['type'] = empty($data['type']) ? ChoiceType::TYPE_CONTAINS : $data['type'];

        if (strlen($data['value']) == 0) {
            return;
        }

        $eb = $proxyQuery->getQueryBuilder()->expr();

        switch ($data['type']) {
            case ChoiceType::TYPE_EQUAL:
                $expr = $eb->eq($field, $data['value']);
                break;
            case ChoiceType::TYPE_NOT_CONTAINS:
                $expr = $eb->textSearch($field, '* -'.$data['value']);
                break;
            case ChoiceType::TYPE_CONTAINS:
                $expr = $eb->like($field, '%'.$data['value'].'%');
                break;
            case ChoiceType::TYPE_CONTAINS_WORDS:
            default:
                $expr = $eb->textSearch($field, $data['value']);
        }

        $proxyQuery->andWhere($expr);
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'format'   => '%%%s%%'
        );
    }

    public function getRenderSettings()
    {
        return array('doctrine_phpcr_type_filter_choice', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }
}
