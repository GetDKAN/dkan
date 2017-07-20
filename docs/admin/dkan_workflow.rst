.. _`user-docs dkan workflow`:

=============
DKAN Workflow
=============

DKAN Workflow is an advanced feature that opens up additional functions to manage an editorial review process for the content on a DKAN site. It also adds a new set of user roles and permissions to distribute the work associated with a review process.

While there are usually only one or two Site Managers maintaining the entire site, content can be added by dozens of different users. In some cases the amount of content that needs review and management may be on an order that cannot be done by just one or two people. DKAN Workflow helps ensure quality content by introducing a review process, and it distributes the workload with unique Workflow roles and permissions.

Special Note: This feature is not enabled by default and requires the DKAN Workflow module to be enabled. If you are interested in getting DKAN Workflow, please talk to your site administrator or developer. 

Workflow and the Editorial Process
----------------------------------

Data portals can house thousands of files in the form of Resources organized into Datasets, and these Datasets and Resources may originate from a variety of departments and organizations. While there may be one or two Site Managers maintaining the entire site, content may be added by dozens of different users. This empowers agencies to add their data as it becomes available and allows for updating the data portal to be a sustainable endeavor.

On the other hand, broad contributions add a complicating factor for Site Managers. Contributors from different Groups can add data to the site, but these users may be unfamiliar with open data standards and less knowledgeable about handling data in general.

DKAN Workflow introduces an editorial process to ensure quality control at any scale. Workflow creates a moderation queue so that content is published only after a designated supervisor has reviewed and approved it. Contributors can still add content to the data catalog, but it is up to a supervisor to act as the gatekeeper in making the content public on the live site.


Workflow Roles and Permissions
------------------------------

Each user on a DKAN site will have a role (or multiple roles) and have certain permissions for moving content through Workflow. These roles allow users to interact differently with My Workbench, but they do not negate the need for core user roles. This means that every user must be assigned a core role granting a certain level of access to the site, independent of the user's Workflow role. Read about :ref:`user management in the Site Manager Playbook<user-docs people>` for additional details. 

Core roles/permissions and Workflow roles/permissions serve different purposes but complement one another. Each set of roles has different permissions that enable the user to interact with certain functions on DKAN. As Workflow roles are assigned, the core role equivalent is automatically selected so that there are no gaps in a user’s permissions.

There are three roles with Workflow-specific permissions: Workflow Contributor, Workflow Moderator, Workflow Supervisor.

Workflow Contributor
~~~~~~~~~~~~~~~~~~~~

The Workflow Contributor role has the lowest level of permissions while still gaining access to My Workbench. These users add content to the data catalog, but their content needs approval before it is published and made live. Workflow Contributors can save content as a Draft or move it to Needs Review, but they do not have the power to publish content live.

Workflow Contributors will be assigned the core role of Content Creator.

Workflow Moderator
~~~~~~~~~~~~~~~~~~

The Workflow Moderator reviews the bulk of content that has been added by Workflow Contributors for a single Group and moves it through the publishing pipeline. This role reviews and publishes content--including what they have created themselves--for their Group, rather than the entire site. Read more about how Groups work in the `Group Roles and Permissions section<user-docs people>`. 

Moderators can also unpublish content, leaving it in a Needs Review or Draft state and removing it from public view, or delete content altogether. Workflow Moderators will be assigned the core role of Editor. 

Workflow Moderators ensure that data uploaded to the site does not have any sensitive or private information included within it. They also check whether the file format is listed correctly, that the Resource follows open data best practices, and that it is associated with the correct licenses. Lastly, the Workflow Moderator should look over the Dataset or Resource’s metadata to ensure accuracy and completeness. 

Workflow Supervisor
~~~~~~~~~~~~~~~~~~~

The Workflow Supervisor role has the highest-level permissions within Workflow because users with this role are not restricted to Group to which they belong.

Unlike a Moderator, Supervisors can access content from all Groups and moderate content as needed. However, the primary focus of the Supervisor is overseeing My Workbench for the entire site. In general, this is an administrative role rather than a practical one. Supervisors catch any issues that could otherwise fall through the cracks, especially in the case of content that isn’t associated with a specific Group.

Workflow Supervisors will be assigned the core role of Site Managers.

My Workbench
------------------

My Workbench provides additional content management via a system of “states”. A piece of content could be in either a “draft” state or a “needs review” state before ultimately being published. “Transitions” define in which order content can move from state to state, and who has permissions to do so. As content is drafted, it goes through an editorial workflow managed by trusted roles. 

Content exists in three states:

Draft
  A saved work in progress.
Needs Review
  The author feels the content is ready to go on public on the live site, and would like the supervisor to review it.
Published
  The content is public and visible on the live site.

My Workbench stores unpublished content in the Draft and Needs Review states, while Workflow roles give certain users the ability to moderate content through the editorial workflow. Users can view the state of the content as well as its age. 

DKAN Workflow organizes content into five different tabs: My Content, My Drafts, Needs Review, Stale Drafts, and Stale Reviews.

The Stale Drafts and Stale Reviews tabs contain content that has gone untouched for too long. The default time limit is 72 hours before drafts become stale.

.. image:: ../images/site_manager_playbook/workflow/my_workbench.png
   :alt: my workbench view

For Workflow Moderators reviewing a steady stream of content it’s helpful to know how many pieces of content need to be moderated. In the picture above, note that each tab has a bubble with a number located in the top right corner. This number reflects the total pieces of content within that tab. 

For example, a Workflow Moderator may have two drafts and 10 pieces of content in the Needs Review tab. Two of those drafts may have gone stale and would also appear in the Stale Drafts tab. Three of the reviews may also be stale and would appear both in the Needs Review tab as well as the Stale Reviews tab. The quantities of content within each category will appear as a count at the top of each tab.

Workflow Roles and Permissions At-a-Glance
------------------------------------------

Users assigned a DKAN Workflow role are automatically assigned the corresponding level of core DKAN role. The following is the relationship between the roles.

.. list-table:: 
   :stub-columns: 1
   
   * - Core Role
     - Content Creator
     - Editor
     - Site Manager
   * - Workflow Role
     - Workflow Contributor
     - Workflow Moderator
     - Workflow Supervisor

Overview of workflow permissions:

+---------------+-------------------------------------+---------------------------------------------------------------------------------------------------+
| Tab Name      | Role (Users that can view the tab)  | Tab Function                                                                                      |
+===============+=====================================+===================================================================================================+
| My Content    | All Workflow Roles                  | All of the content that a user has authored, in any publishing stage.                             |
+---------------+-------------------------------------+---------------------------------------------------------------------------------------------------+
| My Drafts     | All Workflow Roles                  | All of the user's own drafts.                                                                     |
+---------------+-------------------------------------+---------------------------------------------------------------------------------------------------+
| Needs Review  | All Workflow Roles                  | For Workflow Contributors, this will be content that they have moved to the Needs Review state.   |
|               |                                     |                                                                                                   |
|               |                                     | Workflow Moderators see Needs Review content for their specific Group.                            |
|               |                                     |                                                                                                   |
|               |                                     | Workflow Supervisors see Needs Review content for the entire site.                                |
+---------------+-------------------------------------+---------------------------------------------------------------------------------------------------+
| Stale Drafts  | Workflow Moderators and Supervisors | All drafts that are more than 72 hours old.                                                       |
+---------------+-------------------------------------+---------------------------------------------------------------------------------------------------+
| Stale Reviews | Workflow Moderators and Supervisors | All Needs Review content that has been in that state for more than 72 hours.                      |
+---------------+-------------------------------------+---------------------------------------------------------------------------------------------------+

Using Workflow
--------------

Add Content
~~~~~~~~~~~

Adding content with Workflow enabled is similar to the general process for adding content. 

1. From the **Admin Menu** hover over the **Add Content** menu link. 
2. From the drop-down menu, select the content type to add. By default, only Resources and Datasets may be moderated as part of Workflow. 
3. Fill out the details of the content type. 
4. At the bottom of the page, click the **Publishing options** menu item. 
5. In this menu, users can change the state of the content. Workflow Supervisors and Moderators can directly publish content, but Contributors may only save content in the Draft or Needs Review states. 
6. Choose the state of the Content and click the **Save** button. 

If the content was saved in a Draft state or moved to the Needs Review state, it will appear in the user's My Workbench. Users can draft content to come back to or move it to the review phase by changing the moderation state at any time.

Content may cycle back and forth between draft and review as it goes through the revision process.

Moderate Content
~~~~~~~~~~~~~~~~

All users moderate content in some capacity. Workflow Contributors moderate their content between the Draft state and the Needs Review state. Workflow Moderators are responsible for publishing their own content as well as content created by Workflow Contributors. 

All content in the Workflow pipeline is accessed in My Workbench. From My Workbench users can see at-a-glance a summary of content and the state it's in. 

1. From the Admin Menu click the My Workbench link. 
2. Click on one of the tabs to see all the content in that publishing state. 
3. Workflow Contributors can moderate from this page by clicking the Submit for Review button to send a draft to a Workflow Moderator to review.
4. Workflow Moderators can moderate content from this page by clicking the Reject or Publish buttons on a piece of content.

.. image:: ../images/site_manager_playbook/workflow/my_drafts.png
   :alt: my drafts view

Review content and make changes:

1. From My Workbench, navigate to tab with publishing state of the content. 
2. Click on the title link of the piece of content. 
3. Click the **Edit Draft** button to make changes directly to the content. 
4. At the bottom of the page, moderate the publishing state. 
5. Workflow Moderators may change the state to draft for revisions or directly publish the content.

Click the **Moderate** button to see the full revision history and change the publishing state.

.. image:: ../images/site_manager_playbook/workflow/moderate.png
   :alt: moderate view
