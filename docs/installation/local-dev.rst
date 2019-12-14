Local Development Environment
=============================

For testing out DKAN locally and doing feature work directly on the software (as opposed to working on a particular, 
customized website), using a standardized, **docker**-based local environment is recommended. 
This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of 
our continuous integration tools.

These instructions are geared toward people who want to contribute improvements or fixes to DKAN core. 
Once you have a working local copy, please make contributions using the 
`standard fork and pull-request workflow in Gitihub <https://help.github.com/categories/collaborating-with-issues-and-pull-requests/>`_.

DKAN Tools
----------

This CLI application provides tools for implementing and developing `DKAN <https://getdkan.org/>`_, the Drupal-based open data portal.

Requirements
^^^^^^^^^^^^

`DKAN Tools <https://github.com/GetDKAN/dkan-tools>`_ was designed with a **Docker**-based local development environment in mind. Current requirements are simply:

* Bash-like shell that can execute .sh files (Linux or OS X terminals should all work)
* `Docker <https://www.docker.com/get-docker>`_
* `Docker Compose <https://docs.docker.com/compose/>`_

That's it! All other dependencies are included in the Docker containers that dkan-tools will create.

It is also possible to run most DKAN Tools commands using a local webserver, database and PHP setup, but this practice is less supported.

Installation
------------

1. Download or clone this repository into any location on your development machine.
2. Add *bin/dktl* to your `$PATH` somehow. This is often accomplished by adding a symbolic link to a folder already in your path, like *~/bin*. For instance, if DKAN Tools is located in */myworkspace*:

.. code-block:: bash

  ln -s  /myworkspace/dkan-tools/bin/dktl ~/bin/dktl


Alternatively, you could add */myworkspace/dkan-tools/bin* directly to your `$PATH`. Enter this in your terminal or add it to your session permanently by adding a line in *.bashrc* or *.bash_profile*:

.. code-block:: bash
   
    export PATH=$PATH:/myworkspace/dkan-tools/bin


Once you are working in a valid project folder (see next section) you can type `dktl` at any time to see a list of available commands.

Starting a project
------------------

To start a project with `dktl`, create a project directory.

.. code-block:: bash

    mkdir my_project && cd my_project


Inside the project directory, initialize your project.

.. code-block:: bash

    dktl init


This will automatically start up the Docker containers, which can also be started manually with `dktl docker:compose up -d`. Any other docker-compose commands can be run via `dktl docker:compose <args>` or simply `dktl dc <args>`.

After initialization, we want to get DKAN ready. We can use `git clone` (recommended if you are working directly on DKAN core and will want to commit and push changes to the DKAN project) or download a tarball of the DKAN source from `GitHub <https://github.com/GetDKAN/dkan>`_, but the easiest method is using this command:

.. code-block:: bash

    dktl dkan:get <version_number>


Versions of DKAN look like this: ``7.x-1.15.3``. We can see all of `DKAN's releases <https://github.com/getDkan/dkan/releases>`_ in Github.

Now run the "make" command:

.. code-block:: bash

    dktl make


The `make` command will get all of DKAN's dependencies *including* Drupal core. It will also create all the symlinks necesarry to create a working Drupal site under */docroot*.

Finally, let's install DKAN.

.. code-block:: bash

    dktl install


You can find the local site URL by typing ``dktl docker:surl``.

Structure of a DKAN-Tools-based project
---------------------------------------

One of the many reasons for using DKTL is to create a clear separation between the code specific to a particular DKAN site (i.e. "custom code") and the dependencies we pull in from other sources (primarily, DKAN core and Drupal core).

To accomplish this, DKAN Tools projects will have the following basic directory structure, created when we run ``dktl init``.

.. code-block:: markdown

    ├── dkan              # The upstream DKAN core codebase
    ├── docroot           # Drupal core, and contrib modules not from DKAN
    ├── src               # Site-specific configuration, code and files
    │   ├── make          # Overrides for DKAN and Drupal makefiles
    │   ├── modules       # Symlinked to docroot/sites/all/modules/custom
    │   ├── script        # Deployment script and other misc utilities
    |   └── site          # Symlinked to docroot/sites/default
    │   │   └── files     # The main site files
    │   └── test          # Custom tests
    └── dktl.yml          # DKAN Tools configuration


We may wish to create two additional folders in the root of your project later on: */src/patches*, where we can store local patches to be applied via the make files in */src/make*; and */backups*, where database dumps can be stored. The first time we run `dktl install` the */backups* folder will be created if it does not already exist.

The /src/make folder
^^^^^^^^^^^^^^^^^^^^

DKAN uses `Drush Make <https://docs.drush.org/en/8.x/make/>`_ to define its dependencies. DKAN Tools also uses Drush Make to apply overrides patches to DKAN in a managed way, without having to hack either the Drupal or DKAN core.

DKAN defines its Drupal Core dependency in */dkan/drupal-org-core.make*. Additional DKAN dependencies and patches are defined in */dkan/drupal-org.make*. These two files should not be changed directly within the *dkan* folder, but they can be *overridden* via two files in your project: */src/make/drupal.make* and */src/make/dkan.make*.

If we want to override the version of Drupal being used (for instance, if we need a security update just released in Drupal core but aren't ready to move to the newest DKAN version), we add the right version to */src/make/drupal.make*:

.. code-block:: yaml

    api: 2
    core: 7.x
    projects:
      drupal:
        type: core
        version: '7.50'


In */src/make/drupal.make* we can also define the contributed modules, themes, and libraries that our site uses. For example if our site uses the `Deploy <https://www.drupal.org/project/deploy>`_ module we can add this to */src/make/drupal.make* under the ``projects`` section:

.. code-block:: yaml

    projects:
      deploy:
        version: '3.1'


If our site requires a custom patch to the deploy module, we add it to */src/patches*. For remote patches (usually from `Drupal.org <https://www.drupal.org>`_) we just need the url to the patch:

.. code-block:: yaml

    projects:
      deploy:
        version: '3.1'
        patch:
          1: '../patches/custom_patch.patch'
          3005415: 'https://www.drupal.org/files/issues/2018-10-09/use_plain_text_format-3005415.patch'


The src/site folder
^^^^^^^^^^^^^^^^^^^

Most configuration in Drupal sites is placed in the */sites/default* directory.

The */src/site* folder will replace */docroot/sites/default* once Drupal is installed. */src/site* should then contain all of the configuration that will be in */docroot/sites/default*.

DKTL should have already provided some things in */src/site*: *settings.php* contains some generalized code that is meant to load any other setting files present, as long as they follow the *settings.*\<something\>*.php* pattern. All of the special settings that you previously had in *settings.php* or other drupal configuration files should live in *settings.custom.php* or a similarly-named file in */src/site*.

The src/test folder (custom tests)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

DKAN Tools supports custom PHPUnit and Behat tests found in the *src/test* directory.

If your site does not have tests set up yet, running `dktl init:custom-tests` will set up a *src/test* directory in your project with sample phpunit and behat tests to start from.

To run custom tests:

.. code-block:: bash

    dktl test:phpunit-custom


and

.. code-block:: bash

    dktl test:behat-custom


To manually configure custom phpunit tests (without using ``dktl init:custom-tests``):

1. Create *src/test/phpunit*
2. Place a *phpunit.xml* configuration file in *src/test/phpunit*.  You can use the *phpunit.xml* file in `dkan/test/phpunit <https://github.com/GetDKAN/dkan/blob/7.x-1.x/test/phpunit/phpunit.xml>`_ as an example, replacing the `<testsuite>` elements to reflect your own custom tests. See the `PHPUnit documentation <https://phpunit.readthedocs.io/en/7.0/organizing-tests.html#composing-a-test-suite-using-xml-configuration>`_ for more information.
3. Add a copy of `dkan/test/phpunit/boot.php <https://github.com/GetDKAN/dkan/blob/7.x-1.x/test/phpunit/boot.php>`_ in the same directory.
4. Store your tests in *src/test/phpunit*. Again, use *dkan/test/phpunit* as a guide.


JUnit style test results will be written to *src/test/assets/junit*.

To manually configure Behat tests:

1. Create *src/test/features* directory.
2. Place Behat configuration files *behat.yml* and *behat.docker.yml* in *src/test*.  You can use the corresponding files in `dkan/test <https://github.com/GetDKAN/dkan/tree/7.x-1.x/test>`_ as references, or even just create symbolic links to them.
3. Store you tests in *src/test/features*.

JUnit style test results will be written to *src/test/assets/junit*.

Restoring a database dump or site files
---------------------------------------

DKAN Tools' ``restore`` commands can restore from a local or remote dump of the database, as well as restore a files archive. This simplest way to do this is:

.. code-block:: bash

    dktl dkan:restore --db_url=<path_to_db> --files_url=<path_to_files>


As described below, these options can be stored in a configuration file so that you can type simply ``dktl restore``.

You may also restore from a local database backup, as long as it is placed in a folder under the project root called */backups*. Type ``dktl db:restore`` with no argument, and the backup in */backups* will be restored if there is only one, or you will be allowed to select from a list if there are several.

Create and grab a database dump excluding tables
------------------------------------------------

You can create a database dump excluding tables related to cache, devel, webform submissions and DKAN datastore. Running the command ``dktl site:grab-database @alias`` will create the database backup for the drush alias passed as argument.

This command needs to be run with DKTL_MODE set to "HOST". So you'll need to run ``export DKTL_MODE="HOST"`` and after the command finishes, you should set it back to its old value or just unset the variable by running ``unset DKTL_MODE``.

If you want to import this dump into your local development site, then you can move the file *excluded_tables.sql* into the directory *backups* in the root of your project, then you'll be able to import it by running ``dktl restore:db excluded_tables.sql``.

Configuring DKTL commands
-------------------------

You will probably want to set up some default arguments for certain commands, especially the urls for the ``restore`` command. This is what the dkan.yml file is for. You can provide options for any DKTL command in dkan.yml. For instance:

.. code-block:: yaml

    command:
      restore:
        options:
          db_url: "s3://my-backups-bucket/my-db.sql.gz"
          files_url: "s3://my-backups-bucket/my-files.tar.gz"


If you include this in your dktl.yml file, typing ``dktl restore`` without any arguments will load these two options.

Custom Commands
---------------

Projects to can define their own commands. To create a custom command, create a new class inside of this project with a similar structure to the this one:

.. code-block:: php

    <?php
    namespace DkanTools\Custom;

    /**
    * This is project's console commands configuration for Robo task runner.
    *
    * @see http://robo.li/
    */
    class CustomCommands extends \Robo\Tasks
    {
        /**
        * Sample.
        */
        public function customSample()
        {
            $this->io()->comment("Hello World!!!");
        }
    }


The critical parts of the example are:
1. The namespace
2. The extension of \Robo\Tasks
3. The name of the file for the class should match the class name. In this case the file name should be CustomCommands.php

Everything else (class names, function names) is flexible, and each public function inside of the class will show up as an available ``dktl`` command.


Advanced configuration
----------------------

Disabling ``chown``
^^^^^^^^^^^^^^^^^^^

DKTL, by default, performs most of its tasks inside of a docker container. The result is that any files created by scripts running inside the container will appear to be owned by "root" on the host machine, which often leads to permission issues when trying to use these files. To avoid this DKTL attempts to give ownership of all project files to the user running DKTL when it detects that files have changed, using the `chown` command via `sudo`. In some circumstances, such as environments where ``sudo`` is not available, you may not want this behavior. This can be controlled by setting a true/false environment variable, ``DKTL_CHOWN``.

To disable the ``chown`` behavior, create the environment variable with this command:

.. code-block:: bash

    export DKTL_CHOWN="FALSE"


Running without Docker
^^^^^^^^^^^^^^^^^^^^^^

If for some reason you would like to use some of DKTL without docker, there is a mechanism to accomplish this.

First of all, make sure that you have all of the software DKTL needs:

1) PHP
2) Composer
3) Drush

The mode in which DKTL runs is controlled by an environment variable: ``DKTL_MODE``. To run DKLT without docker set the environment variable to ``HOST``:

.. code-block:: bash

    export DKTL_MODE="HOST"


To go back to running in docker mode, set the variable to `DOCKER` (or just delete it).

Using Xdebug
------------

When using the standard docker-compose environment, `Xdebug <https://xdebug.org/>`_ can be enabled on both the web and CLI containers as needed. Running it creates a significant performance hit, so it is disabled by default. To enable, simply run `dktl xdebug:start`. A new file will be added to */src/docker/etc/php*, and the corresponding containers will restart. In most situations, this file should be excluded from version control with .gitignore.

A note to users of DKAN Starter
-------------------------------

Users of `DKAN Starter <https://github.com/GetDKAN/dkan_starter>`_ will recognize some concepts here. The release of DKAN Tools eliminates the need for a separate DKAN Starter project, as it provides a workflow to build sites directly from DKAN releases. Support for DKAN Starter and its accompanying `Ahoy <http://www.ahoycli.com/en/latest/>`_ commands is ending, and detailed instructions for migrating DKAN Starter projects to the DKAN Tools workflow is coming soon.

Troubleshooting
---------------

===============================================================================   ===================================================================================================
Issue                                                                             Solution
===============================================================================   ===================================================================================================
PHP Warning:  is_file(): Unable to find the wrapper "s3"                          Delete the vendor directory in your local dkan-tools and run ``dktl`` in your project directory
Changing ownership of new files to host user ... chown: ...: illegal group name   Disable the chown behavior ``export DKTL_CHOWN="FALSE"``
===============================================================================   ===================================================================================================
