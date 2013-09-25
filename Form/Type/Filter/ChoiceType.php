<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType as BaseChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends BaseChoiceType
{
    const TYPE_CONTAINS_WORDS = 4;

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'doctrine_phpcr_type_filter_choice';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            self::TYPE_CONTAINS        => $this->translator->trans('label_type_contains', array(), 'SonataAdminBundle'),
            self::TYPE_NOT_CONTAINS    => $this->translator->trans('label_type_not_contains', array(), 'SonataAdminBundle'),
            self::TYPE_EQUAL           => $this->translator->trans('label_type_equals', array(), 'SonataAdminBundle'),
            self::TYPE_CONTAINS_WORDS  => $this->translator->trans('label_type_contains_words', array(), 'SonataAdminBundle'),
        );

        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', $options['field_type'], array_merge(array('required' => false), $options['field_options']))
        ;
    }
}
