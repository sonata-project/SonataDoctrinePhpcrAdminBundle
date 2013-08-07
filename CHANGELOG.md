Changelog
=========

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
