Installation Basics
===================

Before getting started, it's recommended that you familiarize yourself
with:

-  `Drush, the command line tool <http://docs.drush.org/en/master/>`_
-  `Drupal's installation
   process <https://www.drupal.org/documentation/install>`_
-  `Drupal's upgrade process <https://www.drupal.org/upgrade>`_
-  `Drupal profiles and
   distributions <https://www.drupal.org/node/1089736#distributions-vs-installation-profiles>`_

What you will find in the main `DKAN
Repository <https://github.com/GetDKAN/dkan2>`__ is a Drupal
*installation profile*. To set up a working website using DKAN, you will
need to acquire or build a full DKAN distribution of Drupal.

.. tip:: `DKAN Tools <https://github.com/GetDKAN/dkan-tools>`_ is project
  containing commands and tools `CivicActions
  <https://civicactions.com/dkan/>`_ uses for our own implementations and
  deployments.

Requirements
------------

Operating Environment
~~~~~~~~~~~~~~~~~~~~~

DKAN is based on Drupal software and -- generally -- runs anywhere
Drupal is supported. This document assumes installation on a Linux-based
Apache webserver using MySQL as a back-end database (aka LAMP server).
For other environments, please see our Alternative Environment Support.

-  MySQL: minimum version 5.5.3+ with PDO
-  before installation, please create one MySQL database and associated
   user.
-  PHP: minimum version 5.5.x
-  Apache: minimum version 2.x
-  Git
-  Drush: minimum version 9.x.

Hardware
~~~~~~~~

-  Minimum RAM: 1GB for development, 2GB or more recommended for
   production.
-  Minimum Disk: 64M for base installation, recommended 1GB or more for
   production.

DKAN is based on Drupal and follows the same basic installation
procedure as any Drupal distribution. More information about various
requirements can be located in the `Drupal Installation
Guide <https://www.drupal.org/documentation/install>`__.

Installation
------------

Using fully made version
~~~~~~~~~~~~~~~~~~~~~~~~

Learn more about: `DKAN2 Starter <https://github.com/getdkan/dkan2-starter>`_

Build your own
~~~~~~~~~~~~~~

**Requirements**

- Install `dkan-tools <https://github.com/GetDKAN/dkan-tools>`_. 
- Set an environment variable called ``DRUPAL_VERSION`` with a value of ``V8``.
    - On the command line, enter ``export DRUPAL_VERSION=V8`` or set in ``.bashrc``
- Setup and start the proxy:
    - Add `dkan` to `/etc/hosts`
    - Start the proxy: 
      ``docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy`` 

**Installation**

1. Create a directory for your project: ``mkdir <directory-name> && cd <directory-name>``
2. Initialize your project with dkan-tools: ``dktl init``
3. Get Drupal: ``dktl get <drupal-version>``
4. Get Drupal dependencies, and install DKAN: ``dktl make``
5. Install DKAN: ``dktl install``
6. Access the site: ``dktl drush uli --uri=dkan``

Dummy content
~~~~~~~~~~~~~

To populate your site with example content, and then remove it:

::

    $ drush dkan-dummy-content:create
    $ drush dkan-dummy-content:remove
    ...