Installation
=============

.. note::
  `DKAN DDEV Add-on <https://getdkan.github.io/ddev-dkan/>`_ is a project
  containing commands and tools that `CivicActions <https://civicactions.com/dkan/>`_
  uses for our own implementations and deployments.

For either working on a project locally or working on the core DKAN software and libraries, using a standardized, `docker <https://www.docker.com/>`_-based local environment is recommended.

- `Installing Composer <https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx>`_
- `Installing Docker <https://ddev.readthedocs.io/en/latest/users/install/docker-installation/>`_
- `Installing DDEV <https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/>`_

Using `DDEV <https://ddev.readthedocs.io/en/stable/>`_ with the `DKAN DDEV Add-on <https://getdkan.github.io/ddev-dkan/>`_ command-line utility will work on Mac, Linux, or Windows to create containers for the various services needed by DKAN.
This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of our continuous integration tools.

Requirements
------------

DKAN is based on `Drupal software <https://www.drupal.org/docs/getting-started/system-requirements>`_ and -- generally -- runs anywhere Drupal is supported. For the most common setup, we recommend:

-  Drupal 10+
-  MySQL: See minimum requirements for your Drupal version. MariaDB equivilants also supported.
-  PHP: minimum version 8.1
-  Apache: minimum version 2.4.7
-  Drush: minimum version 11.x.
-  Node: minimum version 18 (if using the decoupled frontend)

.. note::
   Drupal 10.4 and 11 support PHP 8.4. While DKAN is compatible with Drupal 10.4
   and 11 as of the 2.20.1 release, it is not tested against PHP 8.4 and may
   have issues.

   DKAN has several functions that expect MySQL/MariaDB database connections;
   running on other databases (e.g. PostgreSQL) is not recommended at this time.

Starting a new DKAN project with DDEV
-------------------------------------

Follow the instructions from the **DKAN DDEV Add-on** documentation: `Starting a new project <https://getdkan.github.io/ddev-dkan/getting-started.html>`_ to generate a new Drupal site with DKAN installed on your system.


Starting a new DKAN project with Composer
-----------------------------------------
DKAN DDEV Add-on bases new projects off of a `composer project <https://github.com/GetDKAN/recommended-project>`_ that you can also use to start a project using your own toolset:

  .. prompt:: bash $

    composer create-project getdkan/recommended-project my-project

Adding DKAN into an existing project
------------------------------------

If you already have an existing Drupal site, install DKAN with `composer <https://www.drupal.org/node/2718229>`_. You can find the `latest DKAN release here <https://github.com/GetDKAN/dkan/releases>`_. Composer will download the module and all of the dependencies it requires for the backend.

  .. prompt:: bash $

      composer require 'getdkan/dkan'
      drush en dkan

.. warning::
   Do note that a bug in Drupal core cron may cause problems with data imports, and applying `this patch <https://www.drupal.org/project/drupal/issues/3274931>`_ is highly recommended. The patch will be applied automatically if you use the `recommended project <https://github.com/GetDKAN/recommended-project>`_.

Add some example datasets to your site
--------------------------------------

  .. prompt:: bash $

      drush en sample_content -y
      drush dkan:sample-content:create
      drush cron

If you have trouble with generating the sample content, check the :doc:`Troubleshooting <../user-guide/guide_dataset>` section in the user guide.

.. note::
   Current DKAN development is utilizing a :ref:`decoupled frontend <decoupled_frontend>`.
   To use Drupal's theme system, there is a dataset twig template
   available in the metastore module. However, views
   integration is a roadmap epic with no target date as of yet.
