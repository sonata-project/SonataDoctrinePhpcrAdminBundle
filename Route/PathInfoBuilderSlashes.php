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

namespace Sonata\DoctrinePHPCRAdminBundle\Route;

use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class PathInfoBuilderSlashes implements RouteBuilderInterface
{
    /**
     * RouteBuilder that allows slashes in the ids.
     *
     * {@inheritDoc}
     */
    function build(AdminInterface $admin, RouteCollection $collection)
    {

        $collection->add('list');
        $collection->add('create');
        $collection->add('batch', null, array(), array('_method' => 'POST'));
        $collection->add('edit', $admin->getRouterIdParameter().'/edit', array(), array('id' => '.+'));
        $collection->add('delete', $admin->getRouterIdParameter().'/delete', array(), array('id' => '.+'));
        $collection->add('export');
        $collection->add('show', $admin->getRouterIdParameter().'/show', array(), array('id' => '.+', '_method' => 'GET'));

        if ($admin->isAclEnabled()) {
            $collection->add('acl', $admin->getRouterIdParameter().'/acl', array(), array('id' => '.+'));
        }

        // add children urls
        foreach ($admin->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }
    }
}
