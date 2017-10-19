Configuration
=============

You provide admin services for document types by tagging them as
``sonata.admin`` with the option ``manager_type: doctrine_phpcr`` and `as usual`_
you can specify a group name and the label for this type.

On this bundle, you can configure templates and the document tree.

.. configuration-block::

    .. code-block:: yaml

        sonata_doctrine_phpcr_admin:
            templates:
                form:
                    # Default:
                    - SonataDoctrinePHPCRAdminBundle:Form:form_admin_fields.html.twig
                filter:
                    # Default:
                    - SonataDoctrinePHPCRAdminBundle:Form:filter_admin_fields.html.twig
                types:
                    list:
                        # Prototype
                        name:                 []
                    show:
                        # Prototype
                        name:                 []
                pager_results:        SonataDoctrinePHPCRAdminBundle:Pager:simple_pager_results.html.twig

            document_tree:
                # See :doc:`document_tree`.
                class:
                    # class names of valid children, manage tree operations for them and hide other children
                    valid_children:       []
                    image:
            document_tree_defaults:  []
            document_tree_options:
                # Depth to which to fetch tree children when rendering the initial tree
                depth:                1
                # Exact check if document has children. For large trees, set to false for better performance, but user might needs to click on expand to see there are no children.
                precise_children:     true
                # Whether moving a node in the tree asks for confirmation.
                confirm_move:         true

.. _`as usual`: http://sonata-project.org/bundles/admin/master/doc/reference/getting_started.html#step-3-create-an-admin-service
