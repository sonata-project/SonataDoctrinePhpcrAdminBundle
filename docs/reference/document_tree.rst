Document Tree
=============

This admin integrates with the `CmfTreeBrowserBundle`_ to provide a tree view
of the documents.

Enable the Bundles
------------------

You need to load two additional bundles to use the tree::

    // app/AppKernel.php
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
                new Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle(),
            );

            // ...
        }
    }

You also need to load the routing of those bundles:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml

        cmf_tree:
            resource: .
            type: 'cmf_tree'

        fos_js_routing:
            resource: "@FOSJsRoutingBundle/Resources/config/routing/routing.xml"

    .. code-block:: xml

        <!-- app/config/routing.xml -->
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

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();

        $collection->addCollection($loader->import('.', 'cmf_tree'));

        $collection->addCollection($loader->import(
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

        #app/config/config.yml
        # ...
        sonata_block:
            blocks:
                # ...
                sonata_admin_doctrine_phpcr.tree_block:
                    settings:
                        id: '/cms'
                    contexts:   [admin]

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
        $container->loadFromExtension('sonata_block', array(
            'blocks' => array(
                // ...
                'sonata_admin_doctrine_phpcr.tree_block' => array(
                    'settings' => array(
                        'id' => '/cms',
                    ),
                    'contexts' => array('admin'),
                ),
            ),
        ));

        $container->loadFromExtension('sonata_admin', array(
            'dashboard' => array(
                'blocks' => array(
                    array('position' => 'left', 'type' => 'sonata_admin_doctrine_phpcr.tree_block'),
                    array('position' => 'right', 'type' => 'sonata.admin.block.admin_list'),
                ),
            ),
        ));

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

        # app/config/config.yml
        sonata_doctrine_phpcr_admin:
            document_tree:
                routing_defaults: [locale]
                repository_name: default
                sortable_by: position

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://sonata-project.org/schema/dic/doctrine_phpcr_admin" />

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
        $container->loadFromExtension('sonata_doctrine_phpcr_admin', array(
            'document_tree' => array(
                'routing_defaults' => array('locale'),
                'repository_name' => 'default',
                'sortable_by' => 'position',
            ),
        ));

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
            'sonata.admin.doctrine_phpcr.tree_controller::tree',
             {
                'root':     basePath ~ "/menu",
                'selected': menuNodeId,
                '_locale':  app.request.locale
            }
        )) %}

    .. code-block:: php

        <?php echo $view['actions']->render(new ControllerReference(
                'sonata.admin.doctrine_phpcr.tree_controller::tree',
                array(
                    'root'     => $basePath . '/menu',
                    'selected' => $menuNodeId,
                    '_locale'  => $app->getRequest()->getLocale()
                ),
        )) ?>

.. note::
    To use the configuration for Symfony < 3.4 you should use the single colon (:) notation to define controller
    actions: ``sonata.admin.doctrine_phpcr.tree_controller:treeAction`` – `jsTree`_

.. _`CmfTreeBrowserBundle`: http://symfony.com/doc/master/cmf/bundles/tree_browser/introduction.html
.. _`cmf-sandbox configuration`: https://github.com/symfony-cmf/cmf-sandbox/blob/master/app/config/config.yml
.. _`jsTree`: http://www.jstree.com/documentation
.. _`Symfony documentation`: https://symfony.com/doc/3.1/controller/service.html#referring-to-the-service
