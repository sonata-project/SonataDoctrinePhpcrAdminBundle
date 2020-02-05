Installation
============

SonataDoctrinePhpcrAdminBundle is part of a set of bundles aimed at abstracting
storage connectivity for SonataAdminBundle. As such, SonataDoctrinePhpcrAdminBundle
depends on SonataAdminBundle, and will not work without it.

.. note::

    These installation instructions are meant to be used only as part of SonataAdminBundle's
    installation process, which is documented `here <http://sonata-project.org/bundles/admin/master/doc/reference/installation.html>`_.

Download the Bundle
-------------------

.. code-block:: bash

    composer require sonata-project/doctrine-phpcr-admin-bundle

Enable the Bundle
-----------------

Then, enable the bundle by adding it to the list of registered bundles
in ``bundles.php`` file of your project::

    // config/bundles.php

    return [
        // ...
        Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle::class => ['all' => true],
        Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle::class => ['all' => true],
    ];

Load Routing
------------

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml

        admin:
            resource: '@SonataAdminBundle/Resources/config/routing/sonata_admin.xml'
            prefix: /admin

        phpcr_admin:
            resource: '@SonataDoctrinePhpcrAdminBundle/Resources/config/routing/tree.xml'
            prefix: /admin

        _sonata_admin:
            resource: .
            type: sonata_admin
            prefix: /admin

    .. code-block:: xml

        <!-- config/routes.xml -->

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="@SonataAdminBundle/Resources/config/sonata_admin.xml"
                prefix="/admin"
            />

            <import
                resource="@SonataDoctrinePhpcrAdminBundle/Resources/config/routing/tree.xml"
                prefix="/admin"
            />

            <import
                resource="."
                type="sonata_admin"
                prefix="/admin"
            />

        </routes>

    .. code-block:: php

        // config/routes.php

        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $routing = $loader->import(
            '@SonataAdminBundle/Resources/config/sonata_admin.xml'
        );
        $routing->setPrefix('/admin');
        $collection->addCollection($routing);

        $routing = $loader->import(
            '@SonataDoctrinePhpcrAdminBundle/Resources/config/routing/tree.xml'
        );
        $routing->setPrefix('/admin');
        $collection->addCollection($routing);

        $_sonataAdmin = $loader->import('.', 'sonata_admin');
        $_sonataAdmin->addPrefix('/admin');
        $collection->addCollection($_sonataAdmin);

        return $collection;

.. _packagist: https://packagist.org/packages/sonata-project/doctrine-phpcr-admin-bundle
