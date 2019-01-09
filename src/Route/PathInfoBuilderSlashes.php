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

namespace Sonata\DoctrinePHPCRAdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class PathInfoBuilderSlashes implements RouteBuilderInterface
{
    /**
     * RouteBuilder that allows slashes in the ids.
     *
     * {@inheritdoc}
     */
    public function build(AdminInterface $admin, RouteCollection $collection): void
    {
        $collection->add('list');
        $collection->add('create');
        $collection->add('batch', null, [], [], [], '', [], ['POST']);
        $collection->add('edit', $admin->getRouterIdParameter().'/edit', [], ['id' => '.+']);
        $collection->add('delete', $admin->getRouterIdParameter().'/delete', [], ['id' => '.+']);
        $collection->add('export');
        $collection->add('show', $admin->getRouterIdParameter().'/show', [], ['id' => '.+'], [], '', [], ['GET']);

        if ($admin->isAclEnabled()) {
            $collection->add('acl', $admin->getRouterIdParameter().'/acl', [], ['id' => '.+']);
        }

        // add children urls
        foreach ($admin->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }
    }
}
