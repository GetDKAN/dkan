=============
DKAN Workflow
=============

Introduction
-------------

For large organizations, it can be difficult to moderate vast amounts of content submitted by a wide range of publishers and agencies.

DKAN Workflow is an optional module for `DKAN
<https://github.com/GetDKAN/dkan>`_ based on the `Workbench
<https://www.drupal.org/project/workbench>`_ family of modules.

With Workflow, site managers and administrators can determine which users are able to add, edit and delete content, as well as which users can view and approve of content under review.

Workflow creates a moderation queue so that content is published to the live site only after a designated supervisor or group moderator has reviewed and approved it. 

**When using DKAN Workflow, content exists in three states:**

* **Draft**  - A saved work in progress.
* **Needs Review** - The author feels the content is ready to go on public on the live site, and would like the supervisor to review it.
* **Published** - The content is public and visible on the live site.

.. figure:: https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan1/workflow/dkan_workflow_screenshot.png

The above image displays what you see on My Workbench after login. The Workbench navigation bar contains your content, drafts, and more. The "Create Content" menu features a list of content types you can create.

There are also three different Workflow roles, each with their own moderation permissions. These roles are Workflow Contributor, Workflow Moderator and Workflow Supervisor. For more on Workflow Roles and Permissions, please skip ahead to the "Workflow Roles and Permissions" section of this document.

Installing Workflow
===================

DKAN Workflow is included on all out-of-the-box DKAN sites; however, it is not enabled by default. It can be enabled either from the Modules management page or by using drush.

.. code-block:: bash

   drush en dkan_workflow -y

Enabling DKAN workflow will automatically enable all other required modules and add the Workflow Supervisor role to all users already assigned the site manager role. (More information available in the :ref:`Workflow Roles <workflow-roles>`
section).

You may also see a message instructing you to rebuild permissions. If so, click the "Rebuild permissions" link to update the node access settings.

.. image:: https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan1/rebuild-permissions-message.png

Requirements for DKAN Workflow
--------------------------------

The DKAN Workflow component as a whole is comprised of three modules:

* DKAN Workflow
* DKAN Workflow Permissions
* Views Workflow List

In addition to these core modules, DKAN Workflow depends on multiple Drupal
contrib modules:

* `Workbench <https://www.drupal.org/project/workbench>`_
* `Workbench Moderation <https://www.drupal.org/project/workbench_moderation>`_
* `Workbench Email <https://www.drupal.org/project/workbench_email>`_
* `Drafty <https://www.drupal.org/project/drafty>`_

Finally, the following Drupal contrib modules provide extra functionality (Menu and link badges, amongst other features):

* `Link Badges <https://www.drupal.org/project/link_badges>`_
* `Menu Badges <https://www.drupal.org/project/menu_badges>`_
* `Better Exposed Filters <https://www.drupal.org/project/better_exposed_filters>`_

All of the aforementioned dependencies are declared in the `drupal-org.make
<https://github.com/GetDKAN/dkan/blob/7.x-1.x/drupal-org.make>`_ file.

.. _workflow-roles:

Workflow Roles and Permissions
==============================

The three Workflow roles correspond with the three core DKAN `roles and permissions <http://dkan.readthedocs.io/en/latest/components/permissions.html>`_ If a user is given a Workflow role, they must also be granted the corresponding core DKAN role.

* **Workflow Contributor = Content Creator**
  
Workflow Contributor is the most basic role; users with this role can add content, save as Draft or move it to Needs Review, but cannot publish content directly to the live site. They can only view content that they've created, and cannot modify the content of others.

* **Workflow Moderator = Editor** 

Workflow Moderator is the middle role, mostly pertaining to moderating specific groups. This role reviews and publishes (or unpublishes) content for their group(s), rather than for the entire site. Workflow Moderators can also view and approve of content that has not yet been assigned to a group.

* **Workflow Supervisor = Site Manager** 
  
Workflow Supervisor is the most powerful role and should only be assigned to highly trusted users. Users with the role of Workbench Supervisor can add, edit, modify, publish, unpublish, moderate or delete _all_ site content. This role is the only role that have access to the "Stale Drafts" and "Stale Review" tabs (more information below).

Here is how core roles in DKAN are automatically correlated to Workbench roles and permissions:

+-------------------------+-------------------------------------+---------------------------------------------+
| What a user will see    | "My Drafts"                         | "Needs Review"                              |
+=========================+=====================================+=============================================+
| Workflow Contributor    |   Only content that they submitted. |   Can see only content they have submitted. |
+-------------------------+-------------------------------------+---------------------------------------------+
| Workflow Moderator      |   The content submitted to their    |   The content submitted to their organic    |
|                         |   organic group.                    |   group.                                    |
|                         |   Their own content.                |   Their own content.                        |
+-------------------------+-------------------------------------+---------------------------------------------+
| Workflow Supervisor     |  Only content that they submitted.  |  All the "Needs review" content.            |
+-------------------------+-------------------------------------+---------------------------------------------+

My Workbench
============

When logged in as a user that has been assigned a Workbench role, the "My Workbench" button will be displayed on the site's main navigation toolbar.

"My Workbench" is also accessible directly via *admin/workbench*.

.. image:: https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan1/workflow/dkan_workflow_main_interface.png

The "My Workbench" Moderation Toolbar
-------------------------------------

:My content: This tab provides a list of all of the content you've created.

:My drafts: Drafts you’ve written and drafts you have permission to view.

:Needs review: Content that Needs Review and can either be published to the live site or sent back to Drafts.

:Stale drafts: This tab contains Drafts that have sat for over 72 hours without a change in moderation state. (The "stale drafts" moderation state, as well as "stale reviews," are only visible to Site Managers.)

:Stale reviews: This tab provides content filed under Needs Review that has sat for over 72 hours without a change in moderation state.

Additional features:
---------------------

**Content Filters:**
Users can filter through content by *Title*, *Type* (Dataset, Resource, Data Story, etc), and *Groups*.

**Bulk updates:**. 
Certain operations such as changing content from Needs Review back to Draft can be applied to multiple items at once.

Editing Content
----------------

If you'd like to change the moderation state of an individual node (such as a dataset or
resource), you can do so while editing the node itself.

Scroll to the bottom of the node's "Edit" page, and look under under the **Publishing options** sidebar; there, you'll see DKAN Workflow moderation state options. 

Authors and reviewers can change the node's
moderation state and add a note about the change via the **Moderation notes**
text area.

.. image:: https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan1/workflow/workflow_node_edit.png

Changing Notification Email Settings
-------------------------------------

For each DKAN Workflow moderation state transition (for example from *Draft* to *Needs Review*, from
*Needs Review* to *Draft*, etc) the users with corresponding Workflow roles will receive a notification via email.

There are three scenarios in which one will receive email pertaining to DKAN Workflow:

1. They are the original content author.
2. They are a Workflow Moderator of a Group that the content has been assigned to.
3. They are a Workflow Supervisor, in general.

Emails will display the context that had triggered the notification as well as links to the
updated content.

Advanced Options
==================

Tweaking the Email template
---------------------------
To change DKAN Workflow moderation email templates, go to the *admin/config/workbench/email* configuration page. For more
in-depth documentation, please review the `Workbench Modules Docs`_.

Workbench Modules Docs
-----------------------

For more information, please refer to the following documentation:

* `Workbench documentation on Drupal.org
  <https://www.drupal.org/documentation/modules/workbench>`_.
* `Workbench Moderation documentation on Drupal.org
  <https://www.drupal.org/documentation/modules/workbench_moderation>`_.
* `Workbench email documentation on Drupal.org
  <https://www.drupal.org/node/2253081>`_.
