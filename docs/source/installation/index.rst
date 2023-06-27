Installation
===============

.. note::
  `DKAN DDev Addon <https://getdkan.github.io/dkan-ddev-addon/>`_ is a project
  containing commands and tools that `CivicActions <https://civicactions.com/dkan/>`_
  uses for our own implementations and deployments.

For either working on a project locally or working on the core DKAN software and libraries, using a standardized, `docker <https://www.docker.com/>`_-based local environment is recommended.

- [Installing Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
- [Installing Docker](https://ddev.readthedocs.io/en/latest/users/install/docker-installation/)
- [Installing DDEV](https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/)

Using `DDEV <https://ddev.readthedocs.io/en/stable/>`_ with the `DKAN DDev Addon <https://getdkan.github.io/dkan-ddev-addon/>`_ command-line utility will work on Mac, Linux, or Windows to create containers for the various services needed by DKAN.
This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of our continuous integration tools.

Requirements
------------

DKAN is based on `Drupal software <https://www.drupal.org/docs/getting-started/system-requirements>`_ and -- generally -- runs anywhere Drupal is supported. For the most common setup, we reccomend:

-  MySQL: minimum version 5.7.8+ with PDO
-  PHP: minimum version 8.0 or 8.1
-  Apache: minimum version 2.4.7
-  Drush: minimum version 10.x.
-  Node: minimum version 16 (if using the decoupled frontend)

Starting a new DKAN project
---------------------------

Follow the instructions from the **DKAN DDev Addon** documentation: `Starting a new project <https://getdkan.github.io/dkan-ddev-addon/getting-started.html>`_ to generate a new Drupal site with DKAN installed on your system.

DKAN DDev Addon bases new projects off of a `composer project <https://github.com/GetDKAN/recommended-project>`_ that you can also use to start a project using your own toolset:

  .. code-block::

    composer create-project getdkan/recommended-project my-project

Or simply create a project however you prefer and add a requirement for `getdkan/dkan`.

.. warning::
   Do note that a bug in Drupal core cron may cause problems with data imports, and applying `this patch <https://www.drupal.org/project/drupal/issues/3274931>`_ is highly recommended. The patch will be applied automatically if you use the `recommended project <https://github.com/GetDKAN/recommended-project>`_.

Adding DKAN into an existing project
----------------------------------------

If you already have an existing Drupal site, install DKAN with `composer <https://www.drupal.org/node/2718229>`_. You can find the `latest DKAN release here <https://github.com/GetDKAN/dkan/releases>`_. Composer will download the module and all of the dependencies it requires for the backend.

  .. code-block::

    composer require 'getdkan/dkan'
    drush en dkan

To start with some example datasets:

  .. code-block::

      drush en sample_content -y
      drush dkan:sample-content:create
      drush cron
