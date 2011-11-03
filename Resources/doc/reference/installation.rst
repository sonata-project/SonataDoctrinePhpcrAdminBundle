Installation
============

First install the Sonata Admin Bundle

Download bundle
---------------

Add the following lines to the file ``deps``::

  [SonataDoctrinePHPCRAdminBundle]
      git=http://github.com/sonata-project/SonataDoctrinePHPCRdminBundle.git
      target=/bundles/Sonata/DoctrinePHPCRAdminBundle

and run::

  bin/vendors install

Configuration
-------------

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

