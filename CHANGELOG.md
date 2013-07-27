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
