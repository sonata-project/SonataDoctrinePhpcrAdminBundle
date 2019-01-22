Filter Field Definition
=======================

These fields are displayed inside the filter box. They allow you to filter
the list of entities by a number of different methods.

A filter instance is always linked to a Form Type, these types are used :

  - sonata_type_filter_number         : display 2 widgets, the operator (>, >=, <= , <, =) and the value
  - sonata_type_filter_default        : display 2 widgets, a hidden operator (can be changed on demand) and the value
  - sonata_type_filter_date           : display 2 widgets, the operator (>, >=, <= , <, =, empty, not empty) and the value
  - doctrine_phpcr_type_filter_choice : displays 2 widgets, the operator (contains, not contains, equal, contains words) and the value

The Form Type configuration is provided by the filter itself. But they can be tweaked in the ``configureDatagridFilters``
process with the ``add`` method.

The ``add`` method accepts the arguments :

  - the field name
  - the filter type     : the filter name
  - the filter options  : the options related to the filter
  - the field type      : the type of widget used to render the value part
  - the field options   : the type options

Available Filter types
----------------------

  - doctrine_phpcr_boolean    : depends on the ``sonata_type_filter_default`` Form Type, renders yes or no field as operator
  - doctrine_phpcr_callback   : depends on the ``sonata_type_filter_default`` Form Type, types can be configured as needed
  - doctrine_phpcr_choice     : depends on the ``sonata_type_filter_default`` Form Type, renders operator (contains, not contains, equal) field
  - doctrine_phpcr_string     : depends on the ``doctrine_phpcr_type_filter_choice`` Form Type, renders operator (contains, not contains, equal, contains words) field
  - doctrine_phpcr_number     : depends on the ``sonata_type_filter_number`` Form Type, renders operator (>, >=, <= , <, =, empty, not empty) field
  - doctrine_phpcr_date       : depends on the ``sonata_type_filter_date`` Form Type, renders operator (>, >=, <= , <, =, empty, not empty) field
  - doctrine_phpcr_nodename   : depends on the ``doctrine_phpcr_type_filter_choice`` Form Type, renders operator (contains, not contains, equal, contains words) field

Example
-------

.. code-block:: php

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('title')
                ->add('enabled')
                ->add('tags', null, [], null, ['expanded' => true, 'multiple' => true])
            ;
        }
    }

Advanced usage
--------------

Filtering by sub entity properties
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you need to filter your base entities by the value of a sub entity property,
you can use the dot-separated notation (note that this only makes sense
when the prefix path is made of entities, not collections)::

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    final class UserAdmin extends AbstractAdmin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('id')
                ->add('firstName')
                ->add('lastName')
                ->add('address.street')
                ->add('address.ZIPCode')
                ->add('address.town')
            ;
        }
    }

Label
^^^^^

You can customize the label which appears on the main widget by using a ``label`` option::

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('tags', null, ['label' => 'les tags'], null, [
                'expanded' => true, 'multiple' => true
            ]);
    }

Callback
^^^^^^^^

To create a custom callback filter, two methods need to be implemented; one to
define the field type and one to define how to use the field's value. The
latter shall return wether the filter actually is applied to the queryBuilder
or not. In this example, ``getWithOpenCommentField`` and ``getWithOpenCommentFilter``
implement this functionality::

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    use Application\Sonata\NewsBundle\Document\Comment;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('title')
                ->add('enabled')
                ->add('tags', null, [], null, ['expanded' => true, 'multiple' => true])
                ->add('author')
                ->add('with_open_comments', 'doctrine_phpcr_callback', [
    //                'callback'   => [$this, 'getWithOpenCommentFilter'],
                    'callback' => function($queryBuilder, $alias, $field, $data) {
                        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
                            return;
                        }

                        $queryBuilder = $proxyQuery->getQueryBuilder();
                        $eb = $queryBuilder->expr();

                        $queryBuilder->andWhere($eb->eq($field, $data['value']));

                        return true;
                    },
                    'field_type' => 'checkbox'
                ])
            ;
        }

        public function getWithOpenCommentFilter($queryBuilder, $alias, $field, $data)
        {
            if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
                return;
            }

            $queryBuilder = $proxyQuery->getQueryBuilder();
            $eb = $queryBuilder->expr();

            $queryBuilder->andWhere($eb->eq($field, $data['value']));

            return true;
        }
    }

Filtering Fields and Case Sensitivity
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The default behaviour when filtering is to compare values in a case sensitive manner.
For example "Test" is not the same as "test". Depending on your use case, you might want case insensitive filtering.
To make the filter case insensitive, use the ``compare_case_insensitive`` option for the string filter::

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    use Application\Sonata\NewsBundle\Document\Comment;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('title', 'doctrine_phpcr_string', ['compare_case_insensitive' => true])
                ->add('author', 'doctrine_phpcr_string', ['compare_case_insensitive' => true])
                ->add('label', 'doctrine_phpcr_string')
            ;
        }
    }
