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

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType as BaseChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends BaseChoiceType
{
    public const TYPE_CONTAINS_WORDS = 4;

    /**
     * {@inheritdoc}
     *
     * @todo Remove when Symfony <2.8 is no longer supported
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'doctrine_phpcr_type_filter_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [
            $this->translator->trans('label_type_contains', [], 'SonataAdminBundle') => self::TYPE_CONTAINS,
            $this->translator->trans('label_type_not_contains', [], 'SonataAdminBundle') => self::TYPE_NOT_CONTAINS,
            $this->translator->trans('label_type_equals', [], 'SonataAdminBundle') => self::TYPE_EQUAL,
            $this->translator->trans('label_type_contains_words', [], 'SonataDoctrinePHPCRAdmin') => self::TYPE_CONTAINS_WORDS,
        ];

        $builder
            ->add('type', SymfonyChoiceType::class, [
                'choices' => $choices,
                'required' => false,
            ])
            ->add('value', $options['field_type'], array_merge(['required' => false], $options['field_options']))
        ;
    }
}
