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

class NodeNameFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data): void
    {
        if (!$data || !\is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim((string) $data['value']);
        $data['type'] = empty($data['type']) ? ChoiceType::TYPE_CONTAINS : $data['type'];

        if (0 == \strlen($data['value'])) {
            return;
        }

        $where = $this->getWhere($proxyQuery);

        switch ($data['type']) {
            case ChoiceType::TYPE_EQUAL:
                $where->eq()->localName($alias)->literal($data['value']);

                break;
            case ChoiceType::TYPE_CONTAINS:
            default:
                $where->like()->localName($alias)->literal('%'.$data['value'].'%');
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
