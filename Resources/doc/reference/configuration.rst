Configuration
=============

You provide admin services for document types by tagging them as
``sonata.admin`` with the option ``manager_type: doctrine_phpcr`` and as usual
you can specify a group name and the label for this type.

See [the general Sonata Admin documentation](http://sonata-project.org/bundles/admin/2-0/doc/index.html)
for the basic information.

We recommend using the ajax_layout and adding the sonata_admin_doctrine_phpcr.tree_block
to get a tree view of your PHPCR content:

    sonata_admin:
        templates:
            # default global templates
            ajax:    SonataAdminBundle::ajax_layout.html.twig
        dashboard:
            blocks:
                # display a dashboard block
                - { position: right, type: sonata.admin.block.admin_list }
                - { position: left, type: sonata_admin_doctrine_phpcr.tree_block }

You have to manually configure what types of documents should be handled in the
tree and which class may accept what classes as children to manage the move
operations. Documents that are not configured as valid child will be hidden in the tree.

    sonata_doctrine_phpcr_admin:
        document_tree:
            Doctrine\PHPCR\Odm\Document\Generic:
                valid_children:
                    - all
            Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page: ~
            Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route:
                valid_children:
                    - Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route
                    - Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RedirectRoute
            Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RedirectRoute:
                valid_children: []
            Symfony\Cmf\Bundle\MenuItem\Document\MenuItem:
                valid_children:
                    - Symfony\Cmf\Bundle\MenuBundle\Document\MenuItem
                    - Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuItem
            Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuItem:
                valid_children:
                    - Symfony\Cmf\Bundle\MenuBundle\Document\MenuItem
                    - Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuItem
