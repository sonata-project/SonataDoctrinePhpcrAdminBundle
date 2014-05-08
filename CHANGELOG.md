Changelog
=========

1.1.0
---------

Release 1.1.0

2014-04-11
----------

- drop Symfony 2.2 compatibility

1.1.0-RC1
---------

2014-03-24
----------

- Updated the dependencies on sonata to use the new SonataCoreBundle.

2014-02-14
----------

- The tree options confirm_move and depth are now grouped in the configuration
  `document_tree_options`, plus there is an additional option
  `precise_children` to further help with flat trees.

1.0.1
-----

2013-11-12
----------

- Added support for the Sonata ACL editor, if that is enabled in the configuration.
  http://www.sonata-project.org/bundles/admin/master/doc/reference/security.html#acl-editor

1.0.0
-----

2013-09-24
----------

- Improved the document tree configuration so that "all" works as it was intended.
  Added validation of the class names used in the tree - if you start seeing
  exceptions that means you have invalid class names in your `document_tree`.

2013-07-27
----------

- Changed show URL from `/<doc-path>` to `<doc-path>/show` as this agrees with the Sonata path.

2013-02-14
----------

Renamed form type `doctrine_phpcr_type_tree_model` to `doctrine_phpcr_odm_tree`. You will need to update your form types / admin classes, e.g. change:

    $builder->add('parent', 'doctrine_phpcr_type_tree_model');
    // to
    $builder->add('parent', 'doctrine_phpcr_odm_tree');

2013-08-06
----------

renamed:    Resources/views/CRUD/edit_orm_many_association_script.html.twig     ->   Resources/views/CRUD/edit_phpcr_many_association_script.html.twig
renamed:    Resources/views/CRUD/edit_orm_many_to_many.html.twig                ->   Resources/views/CRUD/edit_phpcr_many_to_many.html.twig
renamed:    Resources/views/CRUD/edit_orm_many_to_one.html.twig                 ->   Resources/views/CRUD/edit_phpcr_many_to_one.html.twig
renamed:    Resources/views/CRUD/edit_orm_one_association_script.html.twig      ->   Resources/views/CRUD/edit_phpcr_one_association_script.html.twig
renamed:    Resources/views/CRUD/edit_orm_one_to_many.html.twig                 ->   Resources/views/CRUD/edit_phpcr_one_to_many.html.twig
renamed:    Resources/views/CRUD/edit_orm_one_to_one.html.twig                  ->   Resources/views/CRUD/edit_phpcr_one_to_one.html.twig
renamed:    Resources/views/CRUD/list_orm_many_to_many.html.twig                ->   Resources/views/CRUD/list_phpcr_many_to_many.html.twig
renamed:    Resources/views/CRUD/list_orm_many_to_one.html.twig                 ->   Resources/views/CRUD/list_phpcr_many_to_one.html.twig
renamed:    Resources/views/CRUD/list_orm_one_to_many.html.twig                 ->   Resources/views/CRUD/list_phpcr_one_to_many.html.twig
renamed:    Resources/views/CRUD/list_orm_one_to_one.html.twig                  ->   Resources/views/CRUD/list_phpcr_one_to_one.html.twig

    You should change any template extensions accordingly: {% extends 'SonataDoctrinePHPCRAdminBundle:CRUD:...' %}

Renamed blocks in Resources/views/Form/form_admin_fields.html.twig You should change any block overrides accordingly.
