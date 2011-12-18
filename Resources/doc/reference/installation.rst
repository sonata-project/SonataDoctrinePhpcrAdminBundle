Installation
============

First install the Sonata Admin Bundle

With cmf-sandbox
----------------

This bundle is under development. Currently, the best way to try it and see what is going on is to install a [cmf-sandbox](https://github.com/symfony-cmf/cmf-sandbox/).

Follow the README of the sandbox for how to install it and load the fixtures to see some content.


Without cmf-sandbox
-------------------

The PHPCR Admin Bundle depends on PHPCR-ODM and on the SonataAdminBundle which in turn depends on SonatajQueryBundle.
For PHPCR-ODM, follow the instructions at [DoctrinePHPCRBundle](https://github.com/doctrine/DoctrinePHPCRBundle).

Then for the admin bundle, add the following lines to the file ``deps``:

    [SonatajQueryBundle]
        git=http://github.com/sonata-project/SonatajQueryBundle.git
        target=/bundles/Sonata/jQueryBundle

    [SonataAdminBundle]
        git=https://github.com/sonata-project/SonataAdminBundle.git
        target=/bundles/Sonata/AdminBundle

    [SonataDoctrinePHPCRAdminBundle]
        git=http://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle.git
        target=/bundles/Sonata/DoctrinePHPCRAdminBundle

and run::

  bin/vendors install

Next, be sure to enable the bundles in your autoload.php and AppKernel.php
files:

.. code-block:: php

  <?php
  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\jQueryBundle\SonatajQueryBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle(),
          // ...
      );
  }
