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

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceFieldMaskType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $allFieldNames = [];
        foreach ($options['map'] as $value => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                $allFieldNames[$fieldName] = $fieldName;
            }
        }
        $allFieldNames = array_values($allFieldNames);

        $view->vars['all_fields'] = $allFieldNames;
        $view->vars['map'] = $options['map'];
        parent::buildView($view, $form, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove when Symfony <2.8 is no longer supported
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver): void
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'map' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

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
        return 'choice_field_mask';
    }
}
