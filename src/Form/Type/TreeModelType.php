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

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TreeModelType extends AbstractType
{
    /**
     * @var array
     */
    protected $defaults = [];

    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true);
        $builder->setAttribute('root_node', $options['root_node']);
        $builder->setAttribute('select_root_node', $options['select_root_node']);
        $builder->setAttribute('repository_name', $options['repository_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['root_node'] = $form->getConfig()->getAttribute('root_node');
        $view->vars['select_root_node'] = $form->getConfig()->getAttribute('select_root_node');
        $view->vars['repository_name'] = $form->getConfig()->getAttribute('repository_name');
        $view->vars['routing_defaults'] = $this->defaults;
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'doctrine_phpcr_odm_tree',
            'compound' => false,
            'model_manager' => null,
            'class' => null,
            'property' => null,
            'query' => null,
            'choices' => null,
            'root_node' => '/',
            'select_root_node' => false,
            'parent' => 'choice',
            'repository_name' => 'default',
            'preferred_choices' => [],
            'choice_list' => static function (Options $options, $previousValue) {
                return new ModelChoiceList(
                    $options['model_manager'],
                    $options['class'],
                    $options['property'],
                    $options['query'],
                    $options['choices']
                );
            },
        ]);
    }

    /**
     * NEXT_MAJOR: remove this method.
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
        return 'doctrine_phpcr_odm_tree';
    }
}
