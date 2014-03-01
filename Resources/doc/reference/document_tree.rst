Document Tree
=============

This admin integrates with the `CmfTreeBrowserBundle`_ to provide a tree view
of the documents.

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

        sonata_doctrine_phpcr_admin:
            document_tree_defaults: [locale]
            document_tree:
                Doctrine\ODM\PHPCR\Document\Generic:
                    valid_children:
                        - all
                Symfony\Cmf\Bundle\ContentBundle\Doctrine\Phpcr\StaticContent:
                    valid_children:
                        - Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\SimpleBlock
                        - Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ContainerBlock
                        - Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ReferenceBlock
                        - Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ActionBlock
                Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ReferenceBlock:
                    valid_children: []
                # ...

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://sonata-project.org/schema/dic/doctrine_phpcr_admin" />

                <document-tree-default>locale</document-tree-default>

                <document-tree class="Doctrine\ODM\PHPCR\Document\Generic">
                    <valid-child>all</valid-child>
                </document-tree>

                <document-tree class="Symfony\Cmf\Bundle\ContentBundle\Doctrine\Phpcr\StaticContent">
                    <valid-child>Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\SimpleBlock</valid-child>
                    <valid-child>Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ContainerBlock</valid-child>
                    <valid-child>Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ReferenceBlock</valid-child>
                    <valid-child>Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ActionBlock</valid-child>
                </document-tree>

                <document-tree class="Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ReferenceBlock" />

                <!-- ... -->
            </config>
        </container>

    .. code-block:: php

        $container->loadFromExtension('sonata_doctrine_phpcr_admin', array(
            'document_tree_defaults' => array('locale'),
            'document_tree' => array(
                'Doctrine\ODM\PHPCR\Document\Generic' => array(
                    'valid_children' => array(
                        'all',
                    ),
                ),
                'Symfony\Cmf\Bundle\ContentBundle\Doctrine\Phpcr\StaticContent' => array(
                    'valid_children' => array(
                        'Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\SimpleBlock',
                        'Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ContainerBlock',
                        'Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ReferenceBlock',
                        'Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ActionBlock',
                    ),
                ),
                'Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ReferenceBlock' => array(
                    'valid_children' => array(),
                ),
                // ...
        ));

.. tip::

    A real world example can be found in the `cmf-sandbox configuration`_,
    the section ``document_tree`` in ``sonata_doctrine_phpcr_admin``.

Dashboard
---------

This bundle provides a block suitable for showing all documents on the sonata
dashboard. We recommend using the ajax_layout and adding the
``sonata_admin_doctrine_phpcr.tree_block`` to get a tree view of your PHPCR
content:

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            templates:
                # default global templates
                ajax:    SonataAdminBundle::ajax_layout.html.twig
            dashboard:
                blocks:
                    # display a dashboard block
                    - { position: left, type: sonata_admin_doctrine_phpcr.tree_block }
                    - { position: right, type: sonata.admin.block.admin_list }

Instead of using the block, you can also use an action to render an admin tree.
You can specify the tree root and the selected item, allowing you to have different
type of content in your tree. For instance, you could show the tree of menu documents
like this:

.. configuration-block::

    .. code-block:: jinja

        {% render(controller(
            'sonata.admin.doctrine_phpcr.tree_controller:treeAction',
             {
                'root':     basePath ~ "/menu",
                'selected': menuNodeId,
                '_locale':  app.request.locale
            }
        )) %}

    .. code-block:: php

        <?php echo $view['actions']->render(new ControllerReference(
                'sonata.admin.doctrine_phpcr.tree_controller:treeAction',
                array(
                    'root'     => $basePath . '/menu',
                    'selected' => $menuNodeId,
                    '_locale'  => $app->getRequest()->getLocale()
                ),
        )) ?>

.. _`CmfTreeBrowserBundle`: http://symfony.com/doc/master/cmf/bundles/tree_browser/introduction.html
.. _`cmf-sandbox configuration`: https://github.com/symfony-cmf/cmf-sandbox/blob/master/app/config/config.yml
.. _`jsTree`: http://www.jstree.com/documentation
