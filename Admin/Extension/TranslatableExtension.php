<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\DoctrinePHPCRAdminBundle\Admin\Extension;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslatableExtension extends AdminExtension
{
    /**
     * @var array
     */
    protected $availableLocales;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param array $availableLocales
     * @param string $defaultLocale
     * @param RequestStack $requestStack
     */
    public function __construct(array $availableLocales, $defaultLocale, RequestStack $requestStack)
    {
        $this->availableLocales = $availableLocales;
        $this->defaultLocale = $defaultLocale;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    ) {
        $label = $admin->trans('choose_edit_locale', [], 'SonataDoctrinePHPCRAdmin');
        $menuItem = $menu->addChild($label, ['attributes' => ['dropdown' => true]]);

        foreach ($this->availableLocales as $locale) {
            $urlParameter = ['editLocale' => $locale];

            if (is_object($admin->getSubject())) {
                $url = $admin->generateObjectUrl($action, $admin->getSubject(), $urlParameter);
            } else {
                $url = $admin->generateUrl($action, $urlParameter);
            }

            $label = $admin->trans($locale, [], 'SonataDoctrinePHPCRAdmin');
            $menuItem->addChild($label, ['uri' => $url]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $editLocale = $this->getEditLocale();

        $formMapper
            ->add('editLocale', 'hidden', ['data' => $editLocale, 'mapped' => false])
            ->end();
    }

    /**
     * @return string
     */
    protected function getEditLocale()
    {
        $locale = $this->defaultLocale;
        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            $locale = $request->get('editLocale', $locale);
        }

        return $locale;
    }
}
