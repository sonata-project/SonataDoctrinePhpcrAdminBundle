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

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Extension;

use Sonata\CoreBundle\Form\Type\CollectionType as DeprecatedCollectionType;
use Sonata\DoctrinePHPCRAdminBundle\Form\Listener\CollectionOrderListener;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

/**
 * @deprecated since sonata-project/doctrine-phpcr-admin-bundle 2.x
 * NEXT_MAJOR: Remove this class when replace SonataCoreBundle by SonataFormExtension
 * Extend the sonata collection type to sort the collection so the reordering
 * is automatically persisted in phpcr-odm.
 */
class LegacyCollectionTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('doctrine_phpcr' !== $options['sonata_field_description']->getAdmin()->getManagerType() || !$options['sonata_field_description']->getOption('sortable')) {
            return;
        }
        $listener = new CollectionOrderListener($options['sonata_field_description']->getName());
        $builder->addEventListener(FormEvents::SUBMIT, [$listener, 'onSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return self::getExtendedTypes()[0];
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes()
    {
        return [
            DeprecatedCollectionType::class,
        ];
    }
}
