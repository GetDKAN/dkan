Local Development Environment
=============================

For testing out DKAN locally and doing feature work directly on the software (as opposed to working on a particular, customized website), using a standardized, `docker <https://www.docker.com/>`_-based local environment is recommended. This will ensure you have the same setup as DKAN's core developers, and that your environment is very close to that of our continuous integration tools.

These instructions are geared toward people who want to contribute improvements or fixes to DKAN core. Once you have a working local copy, please make contributions using the `standard fork and pull-request workflow in Gitihub <https://help.github.com/categories/collaborating-with-issues-and-pull-requests/>`_.

We use a `Docker Compose <https://docs.docker.com/compose/>`_ stack very similar to the model used by `Docksal <https://docksal.io/>`_, but leveraging the power of the `Ahoy <http://www.ahoycli.com/>`_ CLI automation tool.

Requirements
------------

To get started working on DKAN core with our toolset you will need the following:

* A Linux or Mac computer (Windows support coming soon)
* `Git <https://git-scm.com/downloads>`_
* `Docker CE <https://www.docker.com/community-edition#/download>`_ (reccomended version: 17.12.1-ce)
* `Docker Compose <https://docs.docker.com/compose/install/>`_ (reccomended version: 1.19.0)
* `Ruby <https://www.ruby-lang.org/en/documentation/installation/>`_ (recommended version: 2.3.3p222)
* `Ahoy <http://www.ahoycli.com/en/latest/#installation>`_ (current scripts *require* Ahoy 1.1 and are incompatible with Ahoy 2.x)

Installing DKAN
---------------

First we need to clone the DKAN repo. If you plan to make and contribute changes, using your own fork in place of the main DKAN URL in the example below is recommended.

.. code-block:: bash

   git clone https://github.com/GetDKAN/dkan.git
   cd dkan
   bash dkan-init.sh dkan

The last line in this sequence runs a script that moves the DKAN profile files into a subdirectory of your project root, and adds an Ahoy configuration file to the root.

.. code-block:: bash

   export AHOY_CMD_PROXY=DOCKER

This line can either be executed directly in your command prompt (in which case it will need to be re-entered every time you open a new shell) or added to your shell configuration file (usually ``$HOME/.bashrc`` or ``$HOME/.bash_profile``). What this does is tell Ahoy to execute all commands in the CLI container in Docker, rather than in your local Mac or Linux environment. (Ahoy can be run without docker, but this is not recommended for this project and usually only done in the context of a CI tool like `ProboCI <https://probo.ci>`_.)

.. tip:: If you add the ``AHOY_CMD_PROXY`` environment variable to your ``.bashrc`` or similar file, don't forget to either close and re-open your terminal, or run ``source ~/.bashrc`` before proceeding.

.. code-block:: bash

   ahoy docker up
   ahoy dkan drupal-rebuild

These two commands will fire up your project's Docker containers and run a basic Drupal installation. If this completes without errors you are probably clear to proceed, but you can check your installation by running `ahoy docker url` and testing the URL this produces in your browser. You will notice that your project root now contains a ``/docroot`` folder, where the full Drupal codebase lives.

.. code-block:: bash

   ahoy dkan remake
   ahoy dkan reinstall

Finally, these two commands will build DKAN from your drupal-org.make file, create symlinks so that your `/dkan` folder is available to drupal under `/docroot/profiles/dkan`, and re-run the full Drupal installation process using the DKAN profile. Each of these commands will take several minutes to complete.

Once they do, you can find the URL for your site by typing ``ahoy docker url`` (or ``ahoy docker surl`` for an HTTPS version). Your initial login will be "admin"/"admin".

Basic Usage
-----------

We are in the process of both overhauling and better-documenting many of these tools. More details on these tools are available in the `DKAN Starter documentation <https://dkan-starter.readthedocs.io>`_. Some basic tips:

* Typing ``ahoy`` anywhere within your project will give you a list of available commands.
* To route Drush commands through the docker container, add ``ahoy`` before any command. For instance, to clear the cache, type ``ahoy drush cc all``.
* Use ``ahoy docker up`` and ``ahoy docker stop`` to start and stop the project's Docker containers. Use ``ahoy docker ps`` to see their current state.
* If you want to restore your database to a "clean" state, typing ``ahoy dkan reinstall`` and chosing "y" at the prompt will restore a backup made at the moment the reinstall command was last completed.
* Run ``ahoy dkan remake`` to apply any changes made to the DKAN make files (`drupal-org.make <https://github.com/GetDKAN/dkan/blob/7.x-1.x/drupal-org.make>`_ and `drupal-org-core.make <https://github.com/GetDKAN/dkan/blob/7.x-1.x/drupal-org-core.make>`_).
* If you need direct command-line access to the CLI container, type ``ahoy docker exec bash`` (or replace ``bash`` with any other command as needed).
