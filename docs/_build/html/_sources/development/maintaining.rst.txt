Updating and Maintaining DKAN
=============================

There are several strategies for maintaining your DKAN site. Maintaining
a DKAN site does not differ substantially from `maintaining other Drupal
distributions <https://www.drupal.org/documentation/build/distributions>`__.

Drupal distributions consist of a script that runs at the time of the
installation as well as a set of modules, themes, and libraries that
exist at ``profiles/MY_DISTRIBUTION`` directory. These modules, themes,
and libraries work the same as any other modules, themes, or libraries
that are added to Drupal sites. They are packaged together in the
``profiles`` directory to make it easier to install and maintain.

.. tip:: `DKAN Tools <https://github.com/GetDKAN/dkan-tools>`_ is project
  containing commands and tools that `CivicActions
  <https://civicactions.com/dkan/>`_ uses for our own implementations and
  deployments.


Filesystem Conventions
----------------------

With Drupal's inheritance model mentioned above, it should not be
necessary to place custom code or modules in the ``profiles/contrib/dkan2``
directory. Additional modules, themes, or libraries, or newer versions
of ones already present in ``profiles/contrib/dkan2``, can be placed in
``modules``, ``themes``, or ``libraries``. This will make maintaining your 
DKAN site much easier.

If it is necessary or expedient to overwrite files in the
``profiles/contrib/dkan2`` directory, it is recommended to keep a
`patch <https://ariejan.net/2009/10/26/how-to-create-and-apply-a-patch-with-git/>`__
of the changes. A patch will make it possible to re-apply changes once a
newer version of DKAN is added to the 'profiles/contrib/dkan2' directory.


Primary Maintenance Tasks
-------------------------

By "maintenance" we mean three specific tasks

-  **Upgrading** DKAN to receive new features and bug-fixes
-  **Adding** additional modules or features
-  **Overriding** current modules or functionally

Getting DKAN Updates
--------------------

DKAN uses Drupal versioning standards, with one modification. *Minor*
upgrades to DKAN are released approximately every 4-6 weeks. For
instance, a minor release would move from DKAN 8.x-1.11 to 8.x-1.12.
Starting with version 8.x-1.12, we are adding *patch* releases for
security and bug fixes. For instance, the first patch release between
8.x-1.12 and 8.x-1.13 will be 8.x-1.12.1.

Please note *you can not use* ``drush up`` *with DKAN*. This is because
DKAN is not packaged on Drupal.org.

Basic Upgrades
~~~~~~~~~~~~~~

placeholder

Using drush make
~~~~~~~~~~~~~~~~

placeholder


Advanced Workflows
------------------

placeholder

Overriding current DKAN modules or functionality
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Drupal has an inheritance model that makes it easy to override modules
added to distributions as well as the functionality of other modules.

Note that moving to a different location for an existing, installed
module will require a `Registry
Rebuild <https://www.drupal.org/project/registry_rebuild>`__ to prompt
Drupal to refresh all module paths.
