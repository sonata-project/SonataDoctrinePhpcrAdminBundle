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
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\DoctrinePHPCRAdminBundle\Filter\Filter as BaseFilter;

class BooleanFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data): void
    {
        if (!$data || !\is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        if (\is_array($data['value']) || !\in_array($data['value'], [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)) {
            return;
        }

        $where = $this->getWhere($proxyQuery);
        $where->eq()->field('a.'.$field)->literal(BooleanType::TYPE_YES == $data['value'] ? true : false);

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
        return ['sonata_type_filter_default', [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => 'hidden',
            'operator_options' => [],
            'label' => $this->getLabel(),
        ]];
    }
}
