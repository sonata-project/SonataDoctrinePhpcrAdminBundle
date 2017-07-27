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

Use composer:

.. code-block:: bash

    php composer.phar require sonata-project/doctrine-phpcr-admin-bundle

You'll be asked to type in a version constraint. 'dev-master' will get you the
latest, bleeding edge version. Check packagist_ for the current stable version:

Enable the Bundle
-----------------

Next, be sure to enable the bundle in your AppKernel.php file:

.. code-block:: php

    <?php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            // set up basic doctrine phpcr-odm requirements
            // set up basic sonata requirements
            // ...
            new Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle(),
            new Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle(),
            // ...
        );
    }

.. note::
    Don't forget that, as part of `SonataAdminBundle's installation instructions <http://sonata-project.org/bundles/admin/master/doc/reference/installation.html>`_,
    you need to enable additional bundles on AppKernel.php

Load Routing
------------


.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml

        admin:
            resource: '@SonataAdminBundle/Resources/config/routing/sonata_admin.xml'
            prefix: /admin

        _sonata_admin:
            resource: .
            type: sonata_admin
            prefix: /admin

    .. code-block:: xml

        <!-- app/config/routing.xml -->
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
                resource="."
                type="sonata_admin"
                prefix="/admin"
            />

        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $routing = $loader->import(
            "@SonataAdminBundle/Resources/config/sonata_admin.xml"
        );
        $routing->setPrefix('/admin');
        $collection->addCollection($routing);

        $_sonataAdmin = $loader->import('.', 'sonata_admin');
        $_sonataAdmin->addPrefix('/admin');
        $collection->addCollection($_sonataAdmin);

        return $collection;

.. _packagist: https://packagist.org/packages/sonata-project/doctrine-phpcr-admin-bundle
