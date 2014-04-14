<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Listener;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * A listener for the parent form object to reorder a children collection based
 * on the order in the form request, which reflects the frontend order. Just
 * setting the right order will make PHPCR-ODM persist the reorderings.
 *
 * @author David Buchmann <david@liip.ch>
 */
class CollectionOrderListener
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name the form field name used for the collection
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Reorder the children of the parent form data at $this->name.
     *
     * For whatever reason we have to go through the parent object, just
     * getting the collection from the form event and reordering it does
     * not update the stored order.
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm()->getParent();
        $data = $form->getData();

        if (! is_object($data)) {
            return;
        }

        $accessor = PropertyAccess::getPropertyAccessor(); // use deprecated BC method to support symfony 2.2
        $newCollection = $accessor->getValue($data, $this->name);
        if (! $newCollection instanceof Collection) {
            return;
        }
        /** @var $newCollection Collection */

        $newCollection->clear();

        /** @var $item FormBuilder */
        foreach ($form->get($this->name) as $key => $item) {
            if ($item->get('_delete')->getData()) {
                // do not re-add a deleted child
                continue;
            }
            if ($item->getName()) {
                // keep key in collection
                $newCollection[$item->getName()] = $item->getData();
            } else {
                $newCollection[] = $item->getData();
            }
        }
    }
}
