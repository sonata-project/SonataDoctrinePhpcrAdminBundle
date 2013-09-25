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

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Symfony\Cmf\Bundle\TreeBrowserBundle\Tree\TreeInterface;

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;

class TreeModelType extends AbstractType
{
    /**
     * @var array
     */
    protected $defaults = array();

    /**
     * @var TreeInterface
     */
    protected $tree;

    /**
     * @param array $defaults
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @param TreeInterface $tree
     */
    public function setTree(TreeInterface $tree)
    {
        $this->tree = $tree;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true);
        $builder->setAttribute('root_node', $options['root_node']);
        $builder->setAttribute('select_root_node', $options['select_root_node']);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['tree'] = $this->tree;
        $view->vars['root_node'] = $form->getConfig()->getAttribute('root_node');
        $view->vars['select_root_node'] = $form->getConfig()->getAttribute('select_root_node');
        $view->vars['routing_defaults'] = $this->defaults;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template'          => 'doctrine_phpcr_odm_tree',
            'compound'          => false,
            'model_manager'     => null,
            'class'             => null,
            'property'          => null,
            'query'             => null,
            'choices'           => null,
            'root_node'         => '/',
            'select_root_node'  => false,
            'parent'            => 'choice',
            'preferred_choices' => array(),
            'choice_list'       => function (Options $options, $previousValue) {
                return new ModelChoiceList(
                    $options['model_manager'],
                    $options['class'],
                    $options['property'],
                    $options['query'],
                    $options['choices']
                );
            }
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'doctrine_phpcr_odm_tree';
    }
}
