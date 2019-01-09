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
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class StringFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data): void
    {
        if (!$data || !\is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $value = trim($data['value']);
        $data['type'] = empty($data['type']) ? ChoiceType::TYPE_CONTAINS : $data['type'];

        if (0 == \strlen($value)) {
            return;
        }

        $where = $this->getWhere($proxyQuery);
        $isComparisonLowerCase = $this->getOption('compare_case_insensitive');
        $value = $isComparisonLowerCase ? strtolower($value) : $value;
        switch ($data['type']) {
            case ChoiceType::TYPE_EQUAL:
                if ($isComparisonLowerCase) {
                    $where->eq()->lowerCase()->field('a.'.$field)->end()->literal($value);
                } else {
                    $where->eq()->field('a.'.$field)->literal($value);
                }

                break;
            case ChoiceType::TYPE_NOT_CONTAINS:
                $where->fullTextSearch('a.'.$field, '* -'.$value);

                break;
            case ChoiceType::TYPE_CONTAINS:
                if ($isComparisonLowerCase) {
                    $where->like()->lowerCase()->field('a.'.$field)->end()->literal('%'.$value.'%');
                } else {
                    $where->like()->field('a.'.$field)->literal('%'.$value.'%');
                }

                break;
            case ChoiceType::TYPE_CONTAINS_WORDS:
            default:
                $where->fullTextSearch('a.'.$field, $value);
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
            'format' => '%%%s%%',
            'compare_lower_case' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return ['Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType', [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
