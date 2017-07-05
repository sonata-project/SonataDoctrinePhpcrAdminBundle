UPGRADE 2.x
===========

### Deprecated `sonata_doctrine_phpcr_admin.document_tree_defaults` configuration

Use `sonata_doctrine_phpcr_admin.document_tree.routing_defaults` now.

### Deprecated `sonata_doctrine_phpcr_admin.document_tree_repository` configuration

Use `sonata_doctrine_phpcr_admin.document_tree.repository_name` now.

### Deprecated class tree configuration under `sonata_doctrine_phpcr_admin.document_tree`

Completely removed the configuration with the usage of the tree-browser.

### Deprecated `Sonata\DoctrinePHPCRAdminBundle\Datagrid\SimplePager`

When adding pager function you should use `Sonata\AdminBundle\Datagrid\SimplePager` now.

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes. 
You can't extend them anymore, because they are only loaded when running internal tests. 
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).
