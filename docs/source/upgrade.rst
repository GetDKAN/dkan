Upgrading from DKAN 2.x
========================

Background
----------

DKAN 2
######

DKAN 2 was a major rewrite of DKAN 7.x-1.x for Drupal 8, and we misinterpreted a
few aspects of the newly-introduced conventions around object-oriented
programming and PSR namespacing. Specifically, there was an assumption that
submodules could inherit the namespace of their parent module, so that under
``Drupal\dkan`` we could have submodules like ``Drupal\dkan\harvest`` and
``Drupal\dkan\datastore``. This was not the case, and we ended up with some very
generic top-level namespaces like ``Drupal\harvest`` and ``Drupal\datastore``.

Aside from the confusion and aesthetic issues this caused, we also started to
encounter problems with collisions on the Drupal.org module registry. Even if
one were not to install the ``drupal/datastore`` module, Drupal's packaging
tools caused issues when people tried to create DKAN extensions to release on
Drupal.org that had dependencies on specific DKAN submodules.

Parallel to this, there has been a long-standing desire to release DKAN on
Drupal.org. The 7.x-1.x version of DKAN did not meet the requirements to publish
there, due to some licensing issues with some of its dependencies. We got into
the habit of developing DKAN on GitHub and releasing via `packagist <https://packagist.org/>`_,
and this continued when we moved to DKAN 2 and Drupal 8, even though 
licensing was no longer an issue.

Getting it right in DKAN 4
##########################

In 2025 we decided to "rip the band-aid off" and rename our submodules to meet
namespacing conventions. This is a much bigger change than it might initially
sound like, as it requires:

#. Changing the namespaces in every `use` statement, touching almost every file
#. Designing a multi-step upgrade path so that at no point does Drupal expect a module that is no longer present.
#. Properly handling config from the old modules, and custom config on existing sites that depends on the renamed modules.

For this reason, we are jumping two major versions of DKAN at once.

* **DKAN 2.23** will introduce the new, renamed modules (they will not yet be functional)
* **DKAN 3.0** will move all the code from the old modules to the new, provide all 
  the update functions to migrate existing sites, and deprecate the old modules.
  We do not plan to release any updates to 3.0, as it is intended purely as a
  transition to DKAN 4.0.
* **DKAN 4.0** will remove the legacy modules from the codebase. This will also
  be the first release on drupal.org, and the package name in composer will change
  from ``getdkan/dkan`` to ``drupal/dkan``.

Soon after 4.0.0 is released we will wind  down work on the 2.x branch. We will
consider backports and especially security updates but expect most new bugfixes
and new features exclusively on DKAN 4.x.

Detailed instructions
---------------------

Summary
#######

Due to limitations in how database and config updates are performed by Drupal,  
it will not be possible to upgrade directly from 2.23 to 4.  It **must** be done 
in separate upgrades. The process as described here may seem intimidating. 
However, it is fairly straightforward:

* Upgrade to latest 2.x
* Upgrade to 3.0
* Upgrade to latest 4.x

.. caution::
   
   This process makes many non-recoverable changes to your database. Do at least
   one dry-run of this in a development environment before attempting in
   production. It is also highly recommended that you take backups at each step
   to ensure you can roll back if something goes wrong.

.. admonition:: Deployment notes

    In most workflows for a site in production, you will not be performing
    composer and git commands directly on your production server. Throughout
    these instructions, there are notes like this with additional considerations when
    performing these actions on a codebase you are editing locally but deploying
    to a production server.

Step 1: Get to latest 2.x
#########################

Before jumping a major version, make sure you are on the latest release of DKAN
2.23.x. Make sure your version constraint for ``getdkan/dkan`` is ``~2.23.0``, ``^2``,
or something equivalent.

Run:

.. prompt:: bash $

    composer update getdkan/dkan
    drush update:db

Commit the changes to ``composer.json`` and ``composer.lock`` if these are under
version control.

.. admonition:: Deployment notes
    :class: dropdown

    Under most workflows, you would deploy this latest release to production and
    run the database updates on your server if they go smoothly on your local
    copy. Depending on your setup, this may be something you have to do manually (for instance, by
    visiting /update.php or running ``drush @your_alias update:db``), or it may be
    part of a post-deploy hook or script that runs automatically after any new
    code deployment.

Step 2: Export configuration
############################

It is highly recommended that before upgrading to DKAN 3.x, you export your site
settings using the Drupal UI or the ``drush config:export`` command. If you are
not familiar with this workflow, please read the
`configuration management section <https://www.drupal.org/docs/administering-a-drupal-site/configuration-management>`_
of the Drupal documentation.

.. admonition:: Deployment notes
    :class: dropdown

    Usually, you will be exporting config in your local environment, but you
    want it to reflect the latest state of your production instance. Make sure
    you have a recent copy of the production database before your export,
    particularly one that reflects the updates from Step 1.

If you keep your configuration in version control, commit the changes.

Step 3: Get to DKAN 3.x
#######################

Now you should be ready to upgrade the DKAN module. Run:

.. prompt:: bash $

    composer require getdkan/dkan:~3.0.0 -W

Note that DKAN 3 also uses the now-standalone `JSON Form Widget module <https://www.drupal.org/project/issues/json_form_widget>`_,
which had previously been included as a submodule of DKAN. Adding `-W` to the
composer command ensures that this new dependency will also be added.

.. admonition:: Deployment notes
    :class: dropdown

    Do not deploy these code changes to production until you have completed
    steps 4-7 or you may lose functionality on your live site.

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

The update hooks will also go through each role on your site and remove any
permissions defined in the disabled modules. This prevents the roles from being
deleted completely due to missing dependencies. If you have exported your config,
these permissions should be re-added to the roles when you re-import your config
(see next step).

Step 6: Re-import configuration
###############################

The correct modules should now be enabled and disabled, but your site config is
now in a weird state. Likely, several configurations have been automatically
removed because their dependencies on legacy modules are no longer met.
Fortunately, your original configuration is preserved in your sync directory if you followed step 2.

Before you import, you can inspect the difference between your current site
config and the sync directory by visiting the Drupal UI at ``/admin/config/development/configuration``.
You will possibly see a number of configuration items being added, because they
were removed when the legacy modules were disabled.

DKAN 3.x includes an `event subscriber <https://github.com/GetDKAN/dkan/blob/3.x/modules/dkan_common/src/EventSubscriber/ConfigImportRenameDependenciesSubscriber.php>`_
that modifies config on import to attempt to re-map old module names to new ones. 
Unexpected changes to config you see in this UI screen may be a result of that
subscriber modifying config items on import.

.. seealso::

    For more information on this approach to config migration, see
    the Drupal documentation page for `Configuration Import Export Transformation <https://www.drupal.org/docs/drupal-apis/configuration-api/configuration-import-export-transformation>`_.

When you're ready, import your configuration from your sync directory:

.. prompt:: bash $

    drush config:import

You will see a confirmation screen showing a summarized version of the differences
listed in the UI. Accept the changes and continue.

Step 7: Re-export configuration
###############################

It's time to lock in your migrated config. Now run

.. prompt:: bash $

    drush config:export

...to re-export your config to the sync directory.

If you are managing your sync directory in version control (recommended), you
have another opportunity to inspect what changed before committing.

.. admonition:: Deployment notes
    :class: dropdown

    It is at this point we recommend committing your changes and deploying the
    new commit to production. Your updated DKAN version, refactored custom code,
    and updated configuration files will all hit at the same time. Hopefully,
    at this point your specific deployment workflow will run the database updates
    and re-import the updated site configuration (in that order!).

    Also, you should have performed one or more local dry-runs before attempting
    this in production. Still, if anything goes wrong, be prepared to roll back
    both the code and the database to a previous tag and backup, respectively.

Step 8: DKAN 4.0
################

The newly namespaced modules are now enabled, the legacy modules disabled, and
all config migrated. We can now upgrade to DKAN 4. Note that the vendor name
must also be updated:

.. prompt:: bash $

    composer require drupal/dkan:~4.0.0
    drush update:db

While there should be no pending database updates at this point, it is always a
good idea to run ``drush update:db`` after any change to ``composer.json``.
