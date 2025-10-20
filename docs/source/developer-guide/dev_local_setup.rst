Local development
==================

As of DKAN v2.22, we recommend the `DDEV Drupal Contrib <https://github.com/ddev/ddev-drupal-contrib>`_
add-on and workflow to work directly on the DKAN codebase. This will let you
clone the module directly from its git repository and stand up a minimal site
around it with a flexible stack and no need to manage an entire website
codebase.

Initial setup
-------------

DKAN includes a shell script, ``ddev-init.sh`` that will initialize a ddev
project in the folder you've cloned DKAN to. Follow these steps:

1. Clone https://github.com/GetDKAN/dkan.git to a folder with a name that does
   not conflict with any other DDEV projects you may have running.
2. Navigate into the new folder. You should see both ``dkan.info.yml`` and
   ``ddev-init.sh`` in the root.
3. Run ``./ddev-init.sh``. If you want to build a specific version of Drupal,
   provide that version as an argument. E.g. ``./ddev-init.sh 11.1``. The default
   is currently 10.4.
4. You now have a full site codebase (the Drupal root) lives in a new subfolder
   of the module called ``web``.
5. Set up a working database by running ``ddev dkan-site-install``.
6. Visit your site by running ``ddev launch`` or log in as user 1 by running
   ``ddev drush uli``.

If you wish to customize more about your environment, such as chosing specific
versions of PHP or MySQL/MariaDB, you may want to examine the ``ddev-init.sh``
script and run its commands individually. Then you can change or add the config
as needed.

.. tip::
  You will generally hit confirmation prompts during the composer install step.
  You can disable these by adding an additional environment variable to your ddev
  config:

  .. code-block:: bash

    ddev dotenv set .ddev/.env.web --composer-no-interaction "1"

Additional DKAN ddev commands
-----------------------------

.. note::
  These commands were mostly inherited from the `ddev-dkan add-on <https://github.com/GetDKAN/ddev-dkan>`_,
  which is now deprecated in favor of the DKAN Drupal Contrib add-on.

DKAN includes a ``.ddev`` folder with a few additional commands that may be of use
to developers:

* ``ddev dkan-sample-content``: Create several sample datasets in DKAN and
  import their data tables to the datastore.
* ``ddev dkan-module-test-cypress``: Run the Cypress end-to-end tests included
  with DKAN. They will be run in headless mode inside the container.
* ``ddev dkan-frontend-install``: Download and install dependencies for the react-based frontend application <https://github.com/GetDKAN/data-catalog-app>`_
  for DKAN.
* ``ddev dkan-frontend-build``: Build the frontend application after installing.
  After running this command, the frontend will be available on your ddev site.
* ``ddev select2``: DKAN uses the Select2 library, to render certain form 
  elements on the metadata edit forms. While there are `several ways to add this
  as a dependency <https://www.drupal.org/project/select2>`_ to your application
  and automate its installation, this command provides a simple way to get it
  working in your development environment.