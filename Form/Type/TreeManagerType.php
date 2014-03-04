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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TreeManagerType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['root'] = $options['root'];
        $view->vars['create_in_overlay'] = $options['create_in_overlay'];
        $view->vars['edit_in_overlay'] = $options['edit_in_overlay'];
        $view->vars['delete_in_overlay'] = $options['delete_in_overlay'];
        parent::buildView($view, $form, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setRequired((array('root')));
        $resolver->setOptional(array('create_in_overlay', 'edit_in_overlay', 'delete_in_overlay'));

        $resolver->setDefaults(array(
            'create_in_overlay' => true,
            'edit_in_overlay' => true,
            'delete_in_overlay' => true,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'doctrine_phpcr_odm_tree_manager';
    }
}
