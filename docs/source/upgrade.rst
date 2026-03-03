Upgrading from DKAN 2.x
========================

Background
----------

DKAN 2
######

DKAN 2 was a major rewrite of DKAN 7.x-1.x for Drupal 8, and we misunderstood a
few aspects of the newly-introduced conventions around object-oriented
programming and PSR namespacing. Specifically, there was an assumption that
submodules could inherit the namespace of their parent module, so that under
``Drupal\dkan`` we could have submodules like ``Drupal\dkan\harvest`` and
``Drupal\dkan\datastore``. This was not the case, and we ended up with some very
generic top-level namepsaces like ``Drupal\harvest`` and ``Drupal\datastore``.

Aside from the confusion and aesthetic issues this caused, we also started to
encounter problems with colisions on the Drupal.org module registry. Even if
one were not to install the ``drupal/datastore`` module, Drupal's packaging
tools caused issues when people tried to create DKAN extensions to release on
Drupal.org that had dependencies on specific DKAN submodules.

Paralell to this, there has been a long-standing desire to release DKAN on
Drupal.org. The 7.x-1.x version of DKAN did not meet the requirements to publish
there, due to some licencing issues with some of its dependencies. We got into
the habit of developing DKAN on GitHub and releasing via packagist, and this
continued when we moved to DKAN 2 and Drupal 8, even though licencing was no
longer an issue.

Getting it right in DKAN 4
##########################

In 2025 we decided to "rip the band-aid off" and rename our submodules to meet
namespacing conventions. This is a much bigger change than it might initially
sound like, as it requires:

#. Changing the namepsaces in every `use` statement, touching almost every file
#. Designing a multi-step upgrade path so that at no point does Drupal expect a module that is no longer present.
#. Properly handling config from the old modules, and custom config on existing sites that depends on the renamed modules.

For this reason, we are jumping two major versions of DKAN at once.

* DKAN 2.23 will introduce the new, renamed modules (they will not yet be functional)
* DKAN 3.0 will move all the code from the new modules to the old, provide all the update functions to migrate existing sites, and deprecate the old modules.
* DKAN 4.0 will remove the legacy modules from the codebase. This will also be the first release on drupal.org, and the package name in composer will change from `getdkan/dkan` to `drupal/dkan`.

Detailed instructions
---------------------

.. warning::
   
   This process makes many non-recoverable changes to your database. Do at least one dry-run of this in a development environment before attempting in production. It is also highly recommended that you take backups at each step to ensure you can roll back if something goes wrong.

Step 1: Get to latest 2.x
#########################

Before jumping a major version, make sure you are on the latest release of DKAN
2.23.x. Make sure your version constraint for ``getdkan/dkan`` is ``~2.23.0``, ``^2``,
or somethiung equivilant.

Run:

.. prompt:: bash $

    composer update getdkan/dkan
    drush update:db

Step 2: Export configuration
############################

It is highly recommended that before upgrading to DKAN 3.x, you export your site
settings using the Drupal UI or the ``drush config:export`` command. If you are
not familiar with this workflow, it is highly recommended that you read
the `configuration management section <https://www.drupal.org/docs/administering-a-drupal-site/configuration-management>`_
of the Drupal documentation.

Step 3: Get to DKAN 3.x
#######################

Now you should be ready to upgrade the DKAN module. Run:

.. prompt:: bash $

    composer require getdkan/dkan:~3.0.0

Note that DKAN 3 also uses the now-standalone `JSON Form Widget module <https://www.drupal.org/project/issues/json_form_widget>`_,
which had previously been included as a submodule of DKAN. Composer should bring
in this new dependency automatically for you.

Step 4: Refactor custom code
############################

If your site contains any custom modules or themes that have dependencies on DKAN,
you will need to update their namespaces and any references to the old module names.
For example, if you had custom code that used the old ``Drupal\harvest`` namespace,
you would need to change it to ``Drupal\dkan_harvest``.

Make sure to update YAML files like ``.info.yml`` and ``.services.yml`` as well.
These may have references to either module names or class namespaces that need to be updated.

Step 5: Run updates
###################

You now have a 3.x codebase but your database has not changed since 2.x. Run:

.. prompt:: bash $

    drush update:db

This will disable all the "legacy" modules (e.g. ``harvest``, ``datastore``, etc)
and enable the new, renamed ones (e.g. ``dkan_harvest``, ``dkan_datastore``).
It will also run all the necessary update hooks to migrate the old modules'
settings to the new modules.

Step 6: Re-import configuration
###############################

The correct modules should now be enabled and disabled, but your site config is
now in a weird state. Likely, several configurations have been automatically
removed because their dependencies on legacy modules are no longer met.
Fortunately, your original configuration is preserved in your sync directory if you followed step 2.

Before you import, you can inspect the difference between your current site
config and the sync directory by visiting the Drupal UI at ``/admin/config/development/configuration``. This will allow you to see any differences and get a sense of what will be changed when you import.

When you're ready, run:

Import your configuration from your sync directory:

.. prompt:: bash $

    drush config:import

