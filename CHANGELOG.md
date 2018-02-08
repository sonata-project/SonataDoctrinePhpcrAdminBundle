# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [2.1.1](https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/compare/2.1.0...2.1.1) - 2018-02-08
### Changed
- Switch all templates references to Twig namespaced syntax
- Switch from templating service to sonata.templating

## [2.1.0](https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/compare/2.0.0...2.1.0) - 2018-01-08
### Added
- version 2.0 for `phpcr-odm`

### Removed
- Support for old versions of PHP and Symfony.

### Fixed
- Missing root_node parameter in cmfTree options

###### Starting this point, the changelog does not follow the same way.

2.0.0
-----

* **2017-04-26**: [BC break] The tree routing is no longer optional. Require `@SonataDoctrinePhpcrAdminBundle/Resources/config/routing/tree.xml` in your routing file.
* **2017-02-06**: [BC break] Removed `Datagrid\SimplePager` in favor of the one provided by the SonataAdminBundle.
* **2015-05-06**: [BC break] Removed `Tree\PhpcrOdmTree`.
* **2015-05-06**: [BC break] Changed first argument of `Controller\TreeController` from `TreeInterface $tree` to `$repositoryName = 'default'`.
* **2015-05-06**: [BC break] The tree block template now only recieves a `repository_name`, `root_node` and `routing_defaults` variables.
* **2015-05-06**: Added `document_tree_repository` setting.
* **2015-05-06**: [BC break] Removed `Form\Type\TreeModelType#setTree()` method and `$tree` property.

1.2.0
-----

* **2014-10-22**: Updated the template `Block/tree.html.twig`, parameters are now wrapped in `settings`.
* **2014-10-13**: Fixed things around showing relations
* **2014-09-29**: Now compatible with sonata admin 2.3

1.2.0-RC1
---------

* **2014-08-20**: Updated to PSR-4 autoloading

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
