<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Extension;

use Doctrine\Common\Util\Debug;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

use Sonata\DoctrinePHPCRAdminBundle\Form\Listener\CollectionOrderListener;

/**
 * Extend the sonata collection type to sort the collection so the reordering
 * is automatically persisted in phpcr-odm.
 */
class CollectionTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('doctrine_phpcr' != $options['sonata_field_description']->getAdmin()->getManagerType() || ! $options['sonata_field_description']->getOption('sortable')) {
            return;
        }
        $listener = new CollectionOrderListener($options['sonata_field_description']->getName());
        $builder->addEventListener(FormEvents::SUBMIT, array($listener, 'onSubmit'));
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'sonata_type_collection';
    }
}
