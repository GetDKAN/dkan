Local Development Environment
=============================

For testing out DKAN locally and doing feature work directly on the software (as opposed to working on a particular, customized website), using a standardized, `docker <https://www.docker.com/>`_-based local environment is recommended. This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of our continuous integration tools.

These instructions are geared toward people who want to contribute improvements or fixes to DKAN core. Once you have a working local copy, please make contributions using the `standard fork and pull-request workflow in Gitihub <https://help.github.com/categories/collaborating-with-issues-and-pull-requests/>`_.

Requirements
------------

To get started working on DKAN core with our toolset you will need the following:

* A Linux or Mac computer (Windows support coming soon)
* `Git <https://git-scm.com/downloads>`_
* `Docker CE <https://www.docker.com/community-edition#/download>`_ (reccomended version: 17.12.1-ce)
* `Docker Compose <https://docs.docker.com/compose/install/>`_ (reccomended version: 1.19.0)
* `DKAN Tools <https://github/GetDKAN/dkan-tools>`_ 

Installing DKAN
---------------

This "builds" a full DKAN website codebase from the bleeding-edge
development version of DKAN, by downloading Drupal and all the
additional modules that DKAN needs to run. You may want to use this
method to get recent changes that have not yet been included in an
official release, or to use a branch or forked version of the DKAN
profile.

Note that ``rsync`` is used to copy the DKAN profile inside the Drupal
``/profiles`` folder. You may wish to modify this process to fit your
own development practices.

::

    $ git clone --branch master https://github.com/GetDKAN/dkan2.git
    $ cd dkan
    $ drush make --prepare-install drupal-org-core.make webroot --yes
    $ rsync -av . webroot/profiles/dkan --exclude webroot
    $ drush -y make --no-core --contrib-destination=./ drupal-org.make webroot/profiles/dkan --no-recursion
    $ cd webroot

Once you’ve downloaded the DKAN software, it’s time to install it. If
you’ve previously installed Drupal, this process will be very similar.

::

    $ drush site-install dkan --db-url="mysql://DBUSER:DBPASS@localhost/DBNAME"

You can add the ``--verbose`` switch if you want to see every step. The
installation should end with ``drush`` creating an admin account with a
random password, which will be output in a message to the terminal.