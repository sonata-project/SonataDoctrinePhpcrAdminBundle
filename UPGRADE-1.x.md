UPGRADE 2.0
===========

### Removed deprecated  `Sonata\DoctrinePHPCRAdminBundle\Datagrid\SimplePager`

When adding pager function you have to use `Sonata\AdminBundle\Datagrid\SimplePager` now.

UPGRADE 1.x
===========

### Deprecated `Sonata\DoctrinePHPCRAdminBundle\Datagrid\SimplePager`

When adding pager function you should use `Sonata\AdminBundle\Datagrid\SimplePager` now.

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes. 
You can't extend them anymore, because they are only loaded when running internal tests. 
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).
