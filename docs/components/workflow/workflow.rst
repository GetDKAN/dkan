DKAN Workflow
=============

DKAN Workflow is a Workflow implementation for `DKAN
<https://github.com/NuCivic/dkan>`_ based on `Workbench
<https://www.drupal.org/project/workbench>`_ family of modules.

.. figure:: /images/workflow/dkan_workflow_screenshot.png

  Dkan Workflow main admininstration interface.

The Dkan workflow component comes in the form of three modules:

* Dkan Workflow.
* Dkan Workflow Permissions.
* Views Workflow List.

In addition to those core module. Dkan Workflow depend on multiple Drupal
contrib modules

* `Workbench <https://www.drupal.org/project/workbench>`_.
* `Workbench Moderation <https://www.drupal.org/project/workbench_moderation>`_.
* `Workbench Email <https://www.drupal.org/project/workbench_email>`_.

Outsite of the direct Workbench addons, Dkan Workflow needs additional Drupal
contrib modules to provide extra functionality (Menu and link badges, etc).

* `Link Badges <https://www.drupal.org/project/link_badges>`_.
* `Menu Badges <https://www.drupal.org/project/menu_badges>`_.
* `Better Exposed Filters <https://www.drupal.org/project/better_exposed_filters>`_.

All those dependencies are managed by the `drupal-org.make
<https://github.com/NuCivic/dkan/blob/7.x-1.x/drupal-org.make>`_ make file.

Installation
------------
Dkan workflow is included in the core Dkan install but not enabled by default.
It can be enabled either from the Modules management page or by using drush.

.. code-block:: bash

   drush en dkan_workflow -y

User Permissions Management
---------------------------
Dkan workflow permissions.

Organic Groups integration
--------------------------
* Support for OG while sending emails is supported but not clearly documented.

Extending Dkan Workflow
-----------------------

Adding more transitions
+++++++++++++++++++++++

Tweaking the Email template
+++++++++++++++++++++++++++
More information should be available in `drupal.org workbench_email
documentation page <https://drupal.org/node/2253081>`_.

