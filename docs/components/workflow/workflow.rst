DKAN Workflow
=============

DKAN Workflow is a Workflow implementation for `DKAN
<https://github.com/NuCivic/dkan>`_ based on the `Workbench
<https://www.drupal.org/project/workbench>`_ family of modules.

The goal of this component is help various organisations adhear to an editorial
workflow for metadata publishing by providing content state tracking and
revisioning, state oriented management UI, and access control.

.. figure:: /images/workflow/dkan_workflow_screenshot.png

  Dkan Workflow main admininstration interface.

Requirements
------------

The Dkan workflow component comes in the form of three modules:

* Dkan Workflow.
* Dkan Workflow Permissions.
* Views Workflow List.

In addition to those core module. Dkan Workflow depend on multiple Drupal
contrib modules

* `Workbench <https://www.drupal.org/project/workbench>`_.
* `Workbench Moderation <https://www.drupal.org/project/workbench_moderation>`_.
* `Workbench Email <https://www.drupal.org/project/workbench_email>`_.
* `Drafty <https://www.drupal.org/project/drafty>`_.

Outsite of the direct Workbench addons, Dkan Workflow needs additional Drupal
contrib modules to provide extra functionality (Menu and link badges, etc).

* `Link Badges <https://www.drupal.org/project/link_badges>`_.
* `Menu Badges <https://www.drupal.org/project/menu_badges>`_.
* `Better Exposed Filters <https://www.drupal.org/project/better_exposed_filters>`_.

All those dependencies are declared in the `drupal-org.make
<https://github.com/NuCivic/dkan/blob/7.x-1.x/drupal-org.make>`_ file.

Installation
------------
Dkan workflow is included in the core Dkan install but not enabled by default.
It can be enabled either from the Modules management page or by using drush.

.. code-block:: bash

   drush en dkan_workflow -y

Enabling Dkan workflow should enable all the dependencies modules and update the
user roles (more information available in the `Workflow Roles`_
section).

Metadata Moderation States
--------------------------

There are three default moderations states available by default in Dkan:

Draft
  This is the starter state that the metadata (be it dataset or resource) is in
  when first created by the "Workflow Contributor" ( defined in the `Workflow
  Roles`_ section). The node can be updated and have multiple iteration (or
  revision in the Drupal jargon) without the need to change the state. After the
  author evalute the content is ready for being reviewed. The node moderation
  state can be set to "Needs Review".

Needs Review
  When the content author consider the work to be good enough to be reviewed by
  a Moderator, the node(s) can be set to the *Needs Review*. This will signal to
  available "Workflow Moderator" users that the data is ready to be looked at by
  peers (more information in the `Workflow Roles`_ section).

Published
  When the content is judged being ready for public consumtion. The qualified
  moderator (Take a look at the `Workflow Roles`_ section) can set it to the
  *Published* state. This will make the current revision of the metadata to be
  accessible by all the site visitor and the dataset/resources will be added to
  the search index.

Content Moderation UI
----------------------------

Controlling the moderation state of the various core content types provided by
Dkan can be done from various places.

My Workbench
++++++++++++++++++++++++++++

The main moderation interface is available from the *My Workbench* link from the
navigation bar, or accessable directly via *admin/workbench*.

.. image:: /images/workflow/dkan_workflow_main_interface.png

1. **Moderation Tabs**.
  :My content:
    This tab is the only tab without the moderation table and provides quick
    links to content creation forms.

  :My drafts:
    This will display the draft content authored by the logged in user.

  :Needs review:
    This will display the content with the moderation state set to *Needs
    Review* depending on the Workflow role of the current user (This behavior is
    detailed in the `Workflow Roles`_ section).

  :Stale drafts:
    This moderation tab is equivalent to **My drafts** tabs except that it holds
    all the *draft* content that was not updated in the last **72 hours**. This
    tab is **only accessible by Workflow Supervisor** (see `Workflow Roles`_).

  :Stale reviews:
    This moderation tab is equivalent to **Needs review** tabs except that it
    holds all the *Needs Review* content that was not updated in the last **48
    hours**. This tab is **only accessible by Workflow Supervisor** (see
    `Workflow Roles`_).

2. **Content Filters**. Users can filter through the moderated content by *Title*,
   *Type* (Dataset, Resource, Data Story, etc), and *Groups*.

3. **Bulk updates**. Certain operations like pusblishing or rejection can be
   applyed to all or a selected subset of the content available on the
   moderation tab.

4. **Moderated content Table**. The table will list all the moderated content
   relevent to the tab currently selected. Supports displaying dataset without
   resource or with all it's resources published (5), moderated dataest with
   moderated child resource (6), and even child moderated resource(s) with
   published parent dataset (7).

Node Edit Page
++++++++++++++++++++++++++++

Changing the moderation state for individual nodes (be it a dataset or a
resource) is available via the node edit form at the bottom of the edit page
under the **Publishing options** sidebar. Authors and reviewers can change the
moderation state and add a note about the change via the **Moderation notes**
text area.

.. image:: /images/workflow/workflow_node_edit.png

Workflow Roles
---------------------------
Dkan workflow permissions provides 3 Drupal roles:

Workflow Contributor
  This is the lowest level role desgined with "Content Creator" users in mind,
  with access only to the workflow menu and limited set of admininstration
  pages. The only transitions granted for this role is from "Draft" to "Needs
  Review" and the opposite way from "Needs Review" to "Draft". The only tabs
  available for the "Workflow Contributor" role are the "My Draft" tab and
  "Needs Review tab". Accros all the tabs, a user with this role have access
  only to the content that was authored by him/her.

Workflow Moderator
  This is a more advanced role desgined for "Editor" role. In addition of all
  the capabilities of the "Workflow Contributor" role, A "Workflow Moderator"
  can move content from "Needs review" to "Published". "Workflow Moderator"
  users have access to all the content that is associated to the same Groups
  that they belong to (checkout `Organic Groups integration`_ for more
  information).

Workflow Supervisor
  This is the role associated with "Site Manager" users. In addition to being
  able to view and act upon all the content available on all the tabs (more
  information available in the `Organic Groups integration`_), this role is the
  only role that have access to the "Stale Drafts" and "Stale Review" tabs.

Automatic User Role Assignment
++++++++++++++++++++++++++++++

Users with only workflow roles won't be able to do much in Dkan and need to be
associated to its equivalent core role. The Roles form on the User edit page
supports adding the suited core role when only a Workflow role is checked.

.. figure:: /images/workflow/dkan_workflow_autorole.gif
   :scale: 75

   Automatic core role assignment with workflow roles.

Organic Groups integration
++++++++++++++++++++++++++

Content viewing
~~~~~~~~~~~~~~~

+-------------------------+-------------------------------------+---------------------------------------------+
| What a user will see    | My drafts                           | Needs review                                |
+=========================+=====================================+=============================================+
| Workflow Contributor    | - Only content that they submitted. | * Can see only content they have submitted. |
+-------------------------+-------------------------------------+---------------------------------------------+
| Workflow Moderator      | - The content submitted to their    | - The content submitted to their organic    |
|                         |   organic group.                    |   group.                                    |
|                         | - Their own content.                | - Thier own content.                        |
+-------------------------+-------------------------------------+---------------------------------------------+
| Workflow Supervisor     | - Only content that they submitted. | - All the "Needs review" content.           |
+-------------------------+-------------------------------------+---------------------------------------------+

Emails
~~~~~~~~~~~~~~~

For each state transition (for example from *Draft* to *Needs Review*, from
*Needs Review* to *Draft*, etc) a set of users with workflow roles will be
notifyied by an email notification. The users will be selected following those
rules:

1. Email original content author.
2. Email "Workflow Moderators" that are members of a group that the content have
   been associated to.
3. Email all "Workflow Supervisors".

Emails will have the context triggering the notification with links to the
updated content.

Extending Dkan Workflow
-----------------------

Tweaking the Email template
+++++++++++++++++++++++++++
Changing the email template being sent when a moderation operation is applyed
can be done via the *admin/config/workbench/email* configuration page. For more
in-depth documentations please Review the `Workbench Modules Docs`_.

Workbench Modules Docs
++++++++++++++++++++++
For more advanced edge case writing custom code may be needed. For more
information please refer to the workflow modules documentation.

* `Workbench documentation in drupal.org
  <https://www.drupal.org/documentation/modules/workbench>`_.
* `Workbench Moderation documentation in drupal.org
  <https://www.drupal.org/documentation/modules/workbench_moderation>`_.
* `Workbench Email documentation in drupal.org
  <https://www.drupal.org/node/2253081>`_.

Known Issues
------------

* Transitions config and Emails templates for “Original Author” could not be
  exported due to a bug in workbench_email.
