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

.. tip:: `DKAN Starter <https://github.com/NuCivic/dkan-starter>`_ is project
  containing a prebuilt version of DKAN and the tools `Granicus
  <https://www.granicus.com/>`_ uses for our own implementations and
  deployments. Learn more advanced workflows in that project's
  `documentation <https://dkan-starter.readthedocs.io>`_.


Filesystem Conventions
----------------------

With Drupal's inheritance model mentioned above, it should not be
necessary to place custom code or modules in the ``profiles/dkan``
directory. Additional modules, themes, or libraries, or newer versions
of ones already present in ``profiles/dkan``, can be placed in
``sites/all``.

If it is necessary or expedient to overwrite files in the
``profiles/dkan`` directory, it is recommended to keep a
`patch <https://ariejan.net/2009/10/26/how-to-create-and-apply-a-patch-with-git/>`__
of the changes. A patch will make it possible to re-apply changes once a
newer version of DKAN is added to the 'profiles/dkan' directory.

If DKAN's extensions and customizations of core Drupal are isolated in
``profiles/DKAN``, and your site's particular configuration, files, and
overrides and customizations of DKAN are isolated in ``sites/``,
maintaining your DKAN site will be much easier.

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
instance, a minor release would move from DKAN 7.x-1.11 to 7.x-1.12.
Starting with version 7.x-1.12, we are adding *patch* releases for
security and bug fixes. For instance, the first patch release between
7.x-1.12 and 7.x-1.13 will be 7.x-1.12.1.

Please note *you can not use* ``drush up`` *with DKAN*. This is because
DKAN is not packaged on Drupal.org.

Basic Upgrades
~~~~~~~~~~~~~~

The least complex way to update your DKAN codebase is similar to `an
update of Drupal itself <https://www.drupal.org/node/1494290>`__.

1. Back up your database (just in case!)
2. Copy your ``sites`` folder somewhere safe.
3. Replace your entire codebase with the latest fully built version of
   DKAN from `DKAN DROPS-7 <https://github.com/NuCivic/dkan-drops-7>`__.
4. Check the new versions' `release
   notes <https://github.com/NuCivic/dkan/releases>`__ to see if there
   are any special instructions for updating. (If you are several
   releases behind, you may need to follow instructions for several
   releases).
5. Replace the ``sites`` folder in your new codebase with your old
   ``sites`` folder.
6. Now navigate to *http://yoursite.com/update.php* or run
   ``drush updatedb``.
7. Clear caches by visiting */admin/performance* or running
   ``drush cache-clear all``.
8. Revert all features by visiting */admin/structure/features* or
   running ``drush features-revert-all`` (Use with caution, as this may
   overwrite any DKAN configuration you have overridden and not exported
   to code; see `Features <https://www.drupal.org/project/features>`__
   for more information.)

Note: Occasionally a DKAN component will be moved to a new directory.
This should be explained in the release notes for that version. But if
you get errors related to incorrect location of module files, you may
want to try `rebuilding the
registry <https://www.drupal.org/project/registry_rebuild>`__.

Using drush make
~~~~~~~~~~~~~~~~

We are developing an easier workflow to update DKAN on the command line.
For the time being, the recommended method for updating using the
``drush make`` instructions described in the Installation Instructions
is similar to the process described above.

Assuming you have followed the instructions for ``drush make`` and have
a ``webroot`` folder inside a main clone of the `DKAN
repo <https://github.com/NuCivic/dkan>`__:

1.  Back up your database
2.  Copy your ``sites`` folder somewhere safe.
3.  Remove your webroot folder: ``rm -rf webroot`` (use with caution!)
4.  Check out the new version of DKAN you want to update to:
    ``git checkout tags/7.x-1.12``
5.  ``drush make drupal-org-core.make webroot --yes``
6.  ``rsync -av . webroot/profiles/dkan --exclude webroot``
7.  ``drush make --no-core --contrib-destination=./ drupal-org.make webroot/profiles/dkan --no-recursion --yes``
8.  Replace the ``sites`` folder in your new codebase with your old
    ``sites`` folder.
9.  Check the new versions' `release
    notes <https://github.com/NuCivic/dkan/releases>`__ to see if there
    are any special instructions for updating. (If you are several
    releases behind, you may need to follow instructions for several
    releases).
10. ``drush updatedb``.
11. ``drush cache-clear all``
12. ``drush features-revert-all`` (use with caution).

You can also use this method to upgrade to the most recent
"bleeding-edge" development version of DKAN. Instead of checking out a
specific tag, check out the ``7.x-1.x`` branch in step 3.

Features Module
---------------

DKAN packages much of its configuration using the `Features
module <https://www.drupal.org/project/features>`__.

After DKAN is upgraded DKAN site maintainers may wish to revert some
features in order to take advantage of new functionality. We recommend
using the `Features
Override <https://www.drupal.org/project/features_override>`__ module to
capture overridden features elements to make it easier to revert
Features from DKAN when desired. More documentation on this to come.

Advanced Workflows
------------------

Using a Custom Make file
~~~~~~~~~~~~~~~~~~~~~~~~

DKAN is “built” using a make file and ``drush make``. The
`drupal-org.make <https://github.com/NuCivic/dkan/blob/7.x-1.x/drupal-org.make>`_
file in DKAN contains a list of most of the modules installed in DKAN.

When developing a website for production, it is recommended to keep a
make file for all custom modules added to DKAN. Instead of using
``drush pm-download`` or other means of downloading and adding modules
to ``sites/all``, a make file is kept that has a list of the sites
modules. This enforces some best practices about not overwriting
contributed modules, maintaining patches, and reusability. This make
file along with DKAN’s makefiles also provide a reusable recipe for your
site.

More documentation and automation scripts regarding this process are
under active development and can be viewed here: `DKAN Starter Documentation <http://dkan-starter.readthedocs.io/>`_.

Adding additional modules or features
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

New modules, themes, or libraries should be added to the 'sites/all'
directory. For modules or themes it is often useful to differentiate
"custom" modules from "community" modules. We often have a directory
structure for modules like:

.. csv-table::
   :header: "Location", "Contents"

   "``sites/all/modules/contrib``", "community or contributed modules"
   "``sites/all/modules/custom``", "custom modules"
   "``sites/all/libraries``", "Additional libraries"

Overriding current DKAN modules or functionality
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Drupal has an inheritance model that makes it easy to override modules
added to distributions as well as the functionality of other modules.

Any modules or themes added to ``sites/all`` will override the same
named module as one that is placed in ``profiles/dkan/``.

If a DKAN site maintainer wishes to update a module supplied by DKAN
that module can be placed in “sites/all”. For example if one wished to
update the `Date module <https://www.drupal.org/project/date>`__, if
there is a security update or new release with a certain functionality,
add it to ``sites/all``:

.. csv-table::
   :header: "Location", "Version"

   "``profiles/dkan/modules/contrib/date``", "7.x-1.4"
   "``sites/all/modules/contrib/date``", "7.x-1.5"

In this case, DKAN will use the version 7.x-1.5 and ignore 7.x-1.4.

If, later, you update your site to a version of DKAN that uses Date v.
7.x-1.5, the version in ``sites/all`` should be removed. Be careful to
review your overrides in ``sites/all`` after every DKAN update to ensure
you are not missing important module updates.

Note that moving to a different location for an existing, installed
module will require a `Registry
Rebuild <https://www.drupal.org/project/registry_rebuild>`__ to prompt
Drupal to refresh all module paths.
