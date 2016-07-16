<?php

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
use Sonata\DoctrinePHPCRAdminBundle\Filter\Filter as BaseFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CallbackFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if the filter is not configured with a
     *                                   callable in the 'callback' option field
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!is_callable($this->getOption('callback'))) {
            throw new \RuntimeException(sprintf('Please provide a valid callback for option "callback" and field "%s"', $this->getName()));
        }

        $this->active = call_user_func($this->getOption('callback'), $proxyQuery, $alias, $field, $data) === true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'callback' => null,
            'field_type' => TextType::class,
            'operator_type' => 'hidden',
            'operator_options' => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label' => $this->getLabel(),
        ));
    }
}
