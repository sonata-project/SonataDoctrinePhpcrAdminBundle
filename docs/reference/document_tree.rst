Document Tree
=============

This admin integrates with the `CmfTreeBrowserBundle`_ to provide a tree view
of the documents.

Download the Bundles
--------------------

.. code-block:: bash

    composer require symfony-cmf/tree-browser-bundle

.. code-block:: bash

    composer require friendsofsymfony/jsrouting-bundle

Enable the Bundles
------------------

Then, enable the bundle by adding it to the list of registered bundles
in ``bundles.php`` file of your project::

    // config/bundles.php

    return [
        // ...
        FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
        Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle::class => ['all' => true],
    ];

You also need to load the routing of those bundles:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml

        cmf_tree:
            resource: .
            type: 'cmf_tree'

        fos_js_routing:
            resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

    .. code-block:: xml

        <!-- config/routes.xml -->

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="."
                type="cmf_tree"
            />

            <import
                resource="@FOSJsRoutingBundle/Resources/config/routing/routing.xml"
            />

        </routes>

    .. code-block:: php

        // config/routes.php

        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();

        $collection
            ->addCollection($loader->import('.', 'cmf_tree'))
            ->addCollection($loader->import(
                "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"
            ));

        return $collection;

Dashboard Block
---------------

This bundle provides a block suitable for showing all documents on the sonata
dashboard. We recommend using the ajax_layout and adding the
``sonata_admin_doctrine_phpcr.tree_block`` to get a tree view of your PHPCR
content:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_block.yaml

        sonata_block:
            blocks:
                sonata_admin_doctrine_phpcr.tree_block:
                    settings:
                        id: '/cms'
                    contexts:   [admin]

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

        sonata_admin:
            dashboard:
                blocks:
                    - { position: left, type: sonata_admin_doctrine_phpcr.tree_block }
                    - { position: right, type: sonata.admin.block.admin_list }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://sonata-project.org/schema/dic/block">
                <! ... -->
                <block id="sonata_admin_doctrine_phpcr.tree_block">
                    <setting id="id">/cms</setting>
                    <context>admin</context>
                </block>
            </config>

            <config xmlns="http://sonata-project.org/schema/dic/admin">
                <dashboard>
                    <block position="left" type="sonata_admin_doctrine_phpcr.tree_block"/>
                    <block position="right" type="sonata.admin.block.admin_list"/>
                </dashboard>
            </config>

        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('sonata_block', [
            'blocks' => [
                // ...
                'sonata_admin_doctrine_phpcr.tree_block' => [
                    'settings' => [
                        'id' => '/cms',
                    ],
                    'contexts' => ['admin'],
                ],
            ],
        ]);

        $container->loadFromExtension('sonata_admin', [
            'dashboard' => [
                'blocks' => [
                    ['position' => 'left', 'type' => 'sonata_admin_doctrine_phpcr.tree_block'],
                    ['position' => 'right', 'type' => 'sonata.admin.block.admin_list'],
                ],
            ],
        ]);

Configuring the tree
--------------------

You have to manually configure what types of documents are allowed in the
tree and which class may accept what classes as children to manage the creation
and move operations. Documents that have no configuration entry or are not
configured as valid child of the respective parent document will not be visible
in the tree.

This configuration is global for all your document trees.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_doctrine_phpcr_admin.yaml

        sonata_doctrine_phpcr_admin:
            document_tree:
                routing_defaults: [locale]
                repository_name: default
                sortable_by: position

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://sonata-project.org/schema/dic/doctrine_phpcr_admin"/>

                <document-tree-default>locale</document-tree-default>

                <document-tree class="Doctrine\ODM\PHPCR\Document\Generic">
                    <routing-default>locale</routing-default>
                    <repository-name>default</repository-name>
                    <sortable-by>position</sortable-by>
                </document-tree>

                <!-- ... -->
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('sonata_doctrine_phpcr_admin', [
            'document_tree' => [
                'routing_defaults' => ['locale'],
                'repository_name' => 'default',
                'sortable_by' => 'position',
            ],
        ]);

.. tip::

    A real world example can be found in the `cmf-sandbox configuration`_,
    the section ``document_tree`` in ``sonata_doctrine_phpcr_admin``.

Rendering a Tree Directly
-------------------------

Instead of using the block, you can also use an action to render an admin tree.
You can specify the tree root and the selected item, allowing you to have different
type of content in your tree. For instance, you could show the tree of menu documents
like this:

.. configuration-block::

    .. code-block:: jinja

        {% render(controller(
            'sonata.admin.doctrine_phpcr.tree_controller::treeAction',
             {
                'root':     basePath ~ "/menu",
                'selected': menuNodeId,
                '_locale':  app.request.locale
            }
        )) %}

    .. code-block:: php

        echo $view['actions']->render(new ControllerReference(
            'sonata.admin.doctrine_phpcr.tree_controller::treeAction',
            [
                'root'     => $basePath . '/menu',
                'selected' => $menuNodeId,
                '_locale'  => $app->getRequest()->getLocale(),
            ],
        ));

.. _`CmfTreeBrowserBundle`: http://symfony.com/doc/master/cmf/bundles/tree_browser/introduction.html
.. _`cmf-sandbox configuration`: https://github.com/symfony-cmf/cmf-sandbox/blob/master/app/config/config.yml
.. _`jsTree`: http://www.jstree.com/documentation
.. _`Symfony documentation`: https://symfony.com/doc/3.1/controller/service.html#referring-to-the-service
