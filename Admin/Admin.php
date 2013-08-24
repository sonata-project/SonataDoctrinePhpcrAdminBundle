<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin as BaseAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * Extend the Admin class to incorporate phpcr changes.
 *
 * Especially make sure that there are no duplicated slashes in the generated urls
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class Admin extends BaseAdmin
{
    /**
     * Path to the root node in the repository
     * under which documents of this admin should be created.
     *
     * @var string
     */
    protected $root;

    /**
     * @param string $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @param string $context
     *
     * @return ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = $this->getModelManager()->createQuery($this->getClass(), '', $this->root);

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    /**
     * @param string $name
     * @param mixed $object
     * @param array $parameters
     * @param bool $absolute
     *
     * @return string
     */
    public function generateObjectUrl($name, $object, array $parameters = array(), $absolute = false)
    {
        $parameters['id'] = $this->getUrlsafeIdentifier($object);
        return $this->generateUrl($name, $parameters, $absolute);
    }

    /**
     * @param object $object
     *
     * @return string
     */
    public function id($object)
    {
        return $this->getUrlsafeIdentifier($object);
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        foreach (array('edit', 'create', 'delete') as $name) {
            if ($collection->has($name)) {
                $collection->get($name)->addOptions(array('expose' => true));
            }
        }
    }
}

