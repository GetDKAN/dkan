Groups in DKAN
==============

**Groups** allow you to group together datasets under an organization (i.e. Parks and Recreation Department, Department of Education) or category (e.g. Transport Data, Health Data) in order to make it easier for users to browse datasets by theme.

As a best practice, datasets and resources that are added to a Group should share a common publisher.

Essentially, Groups are both a way to collect common Datasets and enable an additional workflow on DKAN. On the outward-facing side, site visitors are able to browse and search Datasets published by a specific Group, which is the common publisher of a number of Datasets.

Behind the scenes, Groups add an additional set of roles and permissions that ensure quality and security when publishing data. Group roles and permissions ensure that Content Creators can add new data but only to their assigned Group. This is especially important for large sites that may have several working groups publishing data to the site. By adding users to the Group’s membership roster, you can create and manage a community based around the data within the group.

How to use Groups
-----------------

Adding a new Group
******************
When adding a new Group, the form has fields for basic information about the Group itself that should tell site visitors what to expect from the Datasets in the Group.

:Title: Name your Group to reflect the agency or whoever the common data publisher is for the datasets that will belong to the Group.

:Image: The image here acts like the logo for your Group. It appears on the overview Groups page as well as the individual page of the Group itself. It's best to choose a square image to fit the dimensions of the thumbnail. Whether you choose an image, a logo, or an icon you can use any image that meets the size and file type requirements. As a Site Manager, you may want to add generic icons to the Groups you add if a current logo is unavailable.

:Description: This text is the full description for your Group similar to an "About" page. The description includes details about the agency, its goals, and information about the data it publishes. While you want to include all the relevant information of the Group, the best descriptions are 1-2 paragraphs long and include a link to the agency's main web page for more details.

:Summary text: You can use the Summary to create unique text for your Group. This text appears as a snippet under the Group image on the Group overview page. If left blank the first portion of the body text will be used (about 100 words). Including a summary can be useful in adding more key search terms or using a different tone to intrigue site visitors to learn more.

.. figure:: https://dkan-documentation-files.s3.us-east-2.amazonaws.com/dkan1/group.png

Managing Groups and Members
---------------------------

Once you've added a new Group, you can assign Datasets (and their Resources) to that Group. You can also manage the members of a Group, adding new members and giving certain members different roles. Members of a Group are bound by the permissions of their role and restricted to the content in their Group. As a Site Manager you have access to all Groups and are not limited by the permissions of the Group.

.. _group_roles_permissions:

Roles and Permissions
*********************

With large sites there is often a need to have special permissions for a group of users to handle a specific set of content. Think of a large agency or department with sub-departments or programs that produce content. On the one hand these users shouldn’t have the ability to manage or edit content for the entire site or other Groups. On the other hand it would be impractical for Editors or Site Managers to handle content for a large number of users. To keep content organized and in the hands of its owners without introducing the risk of inadvertent (and sometimes irreversible) actions, Group-level permissions give users the ability to do things they couldn’t necessarily do on the site outside of the Group.

Within Groups there are different levels of access a user can have, which determines another level of permissions. Any user who belongs to a group falls into one of two types: Member or Administrator Member. Users not in the group are considered non-members.

:Non-Member: A non-member is any user on the site who does not belong to the Group. This role can request membership in the Group and view Group content.

:Member: A Member is a basic user within the Group who is mostly adding and editing their own content for the Group.

:Administrator Member: An Administrator Member is able to add and remove Group members and manage (create/edit/delete) all content within the Group. It’s good practice to have only 1 or 2 users in this role for any given Group.

Managing users
**************

Read more about :ref:`adding and managing group members here <manage_group_members>`.
