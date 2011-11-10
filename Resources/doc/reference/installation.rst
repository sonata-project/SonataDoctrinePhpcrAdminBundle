Installation
============

First install the Sonata Admin Bundle

With cmf-sandbox
----------------

This bundle is under development. Currently, the best way to try it and see what is going on is to install a [cmf-sandbox](https://github.com/nacmartin/cmf-sandbox/tree/adminbundle).

run::

    git clone git://github.com/nacmartin/cmf-sandbox.git

and switch to branch `adminbundle` and download the vendors, which include this bundle::

    git checkout adminbundle
    bin/vendors install

There are some dependencies that must be fixed manually::

    cd vendor/symfony-cmf/vendor/doctrine-phpcr-odm/lib/vendor/jackalope
    git checkout master
    cd lib/phpcr
    git checkout master

Note that you will need a running instance of a PHPCR implementation, such as [jackalope](https://github.com/jackalope/jackalope).

Without cmf-sandbox
-------------------

Add the following lines to the file ``deps``::

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
          new Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle(),
          // ...
      );
  }

