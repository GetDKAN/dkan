.. _local-dev:

Local development environment
=============================

For either working on a project locally or working on the core DKAN software and libraries, using a standardized, `docker <https://www.docker.com/>`_-based local environment is recommended. Our `DKAN Tools <https://github.com/getdkan/dkan-tools>`_ command-line utility will work on Mac or Linux to create containers for the various services needed by DKAN. This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of our continuous integration tools.

It is possible, though not reccomended, to use DKAN Tools without docker and using your system's native webserver, php, and database tools; see the DKAN Tools documentation.

If you are making changes or improvements to DKAN, contribute back using the `standard fork and pull-request workflow in Gitihub <https://help.github.com/categories/collaborating-with-issues-and-pull-requests/>`_.

Requirements
------------

To get DKAN working on your computer with our toolset you will need the following:

* A Linux or Mac computer (Windows support coming soon)
* `Git <https://git-scm.com/downloads>`_
* `Docker CE <https://www.docker.com/community-edition#/download>`_ (recommended version: 17.12.1-ce)
* `Docker Compose <https://docs.docker.com/compose/install/>`_ (recommended version: 1.19.0)
* `dkan-tools <https://github.com/getdkan/dkan-tools>`_ 

Starting a new project
----------------------

Follow instructions in the `README <https://github.com/getdkan/dkan-tools>`_ to get dkan-tools working on your system. The dkan-tools README is still focused on Drupal 7 ("DKAN 1") usage. While the basic installation instructions are the same for either scenario, installing and working with DKAN requires the following steps.  

1. 

    Assuming you have dkan-tools installed and working locally, you will need to create an environment variable ``DRUPAL_VERSION`` and set it to ``V8``.

    ::

        $ export DRUPAL_VERSION=V8

    Now DTKL in your current session will know to use only Drupal 8 commands. If you are only using DKAN2 and are not using dkan-tools to manage any Drupal 7-based DKAN projects, you may want to add the same ``export`` command to your .bashrc or .bash_profile file.

2. Create a directory for your project: ``mkdir <directory-name> && cd <directory-name>``
3. Initialize your project with dkan-tools: ``dktl init``

Installing DKAN
---------------

1. Get Drupal: ``dktl get <drupal-version>`` (currnet recommended version: 8.7.3)
2. Get Drupal dependencies, and install DKAN plus the :ref:`frontend application <frontend>`: ``dktl make --frontend``
3. Most likely, dkan-tools will complain that your version of Drush is out-of-date. Upgrade with ``dktl updatedrush``
4. Install DKAN: ``dktl install``
5. Access the site: ``dktl drush uli``

.. note ::

    If you are working directly on the DKAN project or one of its libraries and want to be able to commit changes and submit pull requests, use the ``--prefer-source`` option with ``dktl make``. This option will be passed directly to Composer; see the `Composer CLI documentation <https://getcomposer.org/doc/03-cli.md#command-line-interface-commands>`_ for more details.

.. warning ::

    You should *only* use the built-in ``dktl updatedrush`` command when using our Docker-based environment.

Setting up the front end
~~~~~~~~~~~~~~~~~~~~~~~~

The last step above will provide a link to log in to the new site as "user 1" (admin) without a password, using a port of localhost. This will allow you to create user accounts and content, and otherwise manage your DKAN site using the Drupal administration pages. 

To see your public data portal, however, you will need to set up the `React <https://reactjs.org/>`_-based :ref:`front-end application <frontend>` (a separate software package originally created as part of `Interra <https://github.com/interra/data-catalog-frontend>`_). To do this, set up a proxy that will expose your Drupal installation via the url "http://dkan":

1. Add ``dkan`` to */etc/hosts*
2. Start the proxy:

::

      $ docker run -d -p 80:80 -v /var/run/docker.sock:/tmp/docker.sock:ro jwilder/nginx-proxy

The output of this command will tell you what URL to visit to see the DKAN frontend.