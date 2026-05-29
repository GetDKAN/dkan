Installation
=============

For either working on a project locally or working on the core DKAN software and
libraries, using a standardized, `docker <https://www.docker.com/>`_-based local
environment is recommended. The DKAN core team uses `DDEV <https://ddev.readthedocs.io/en/stable/>`_
for local development of both the DKAN module and DKAN-based web projects.

- `Installing Composer <https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx>`_
- `Installing Docker <https://ddev.readthedocs.io/en/latest/users/install/docker-installation/>`_
- `Installing DDEV <https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/>`_

If you're installing DKAN for local development, to test it and/or contribute to
its codebase, see the :doc:`Local development <../developer-guide/dev_local_setup>`
section of the developer guide.

If you're creating a new web project that will ultimately go into production,
use your workflow of choice for starting a new Drupal project. DKAN is a Drupal
module and can be installed into any Drupal 10+ site using `Composer <https://getcomposer.org/>`_.

DKAN currently lives on Composer's main package repository, and can be added to
a project by running:

  .. prompt:: bash $

    composer require 'getdkan/dkan'

.. warning::
   A bug in Drupal 10.x core cron may cause problems with data imports, and applying `this patch <https://www.drupal.org/project/drupal/issues/3274931>`_ is highly recommended. The patch will be applied automatically if you use the `recommended project <https://github.com/GetDKAN/recommended-project>`_.

Requirements
------------

DKAN is based on `Drupal software <https://www.drupal.org/docs/getting-started/system-requirements>`_ and -- generally -- runs anywhere Drupal is supported. For the most common setup, we recommend:

-  Drupal 10+
-  MySQL: See minimum requirements for your Drupal version. MariaDB equivalents also supported.
-  PHP: minimum version 8.1
-  Apache: minimum version 2.4.7
-  Drush: minimum version 11.x.
-  Node: minimum version 18 (if using the decoupled frontend)

.. note::
   DKAN has several functions that expect MySQL/MariaDB database connections;
   running on other databases (e.g. PostgreSQL) is not recommended at this time.

.. note::
   DKAN requires some additional composer changes due to a dependency on the Select2 library. You should follow the steps in the `Select2 project's Readme <https://git.drupalcode.org/project/select2/-/blob/2.x/README.md?ref_type=heads#installation>`_.


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
