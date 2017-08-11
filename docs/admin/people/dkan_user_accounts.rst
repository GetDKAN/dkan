==================
DKAN User Accounts
==================

On DKAN, what you can do on the site depends on the permissions given to the role assigned to you. These roles can range from general site visitors to trusted Editors working behind the scenes. User roles and permissions maintain the security of your site, distribute workload without compromising quality, and lead to overall better content on your DKAN site.

Roles and Permissions Overview
------------------------------

There are 6 standard roles with set permissions. The following is a list of each role with a description of its purpose and a general description of what the role is able to do. Multiple roles can be assigned to a user, but generally they are in a hierarchy where any higher level role has equal and greater permissions of a lower level role.

Anonymous User
~~~~~~~~~~~~~~

This is any site visitor accessing the site who is not logged in. Anyone who is not authenticated is an anonymous user. It is sometimes useful to log out of your account to view pages as an anonymous user will see them.

**Permissions:**
  - View and search published content

Authenticated user
~~~~~~~~~~~~~~~~~~

All users with login credentials have this role, but the additional functionality is limited. This role is used for a general site visitor who has created an account, but hasn't been given permission to add or edit content on the site. 

**Permissions:**
  - Create a profile

Content Creator
~~~~~~~~~~~~~~~

Content Creators are able to work with the actual content of your DKAN site. This role is good for a user to add their data to your DKAN site, but who doesn’t need to access more sensitive functionality.

**Permissions:**
  - Create most content (Datasets, Resources, Datastories, ...)
  - Edit their own content (cannot edit content created by other users or view unpublished content)
  - Add Resources to the Datastore

Editor
~~~~~~

An Editor is responsible for managing content from a strategic perspective. This role is fit for a user who will create, edit, revise and delete content on a frequent basis, and should be given to a colleague with expertise on the subject matter at hand. This role is able to make changes to content and where it appears, but it doesn't go further into amidninistrative functions.

**Permissions:**
  - Add, edit and delete most content types
  - Cannot create, modify, or delete Groups
  - Cannot modify the roles of other users


Site Manager
~~~~~~~~~~~~

This role is the highest level possible for non-technical users. A Site Manager performs administrative functions, and is a role best suited for a supervisor, manager, or other trusted upper-level employee. The Site Manager is provided with a sweeping overview of the site as well as its content and users. However, they do not deal with the technical back-end.

**Permissions:**
  - Create, edit, and delete all content types created by any user
  - Create and manage groups
  - Change menu structure
  - Administer users
  - Configure Harvests
  - Modify DKAN specific settings

Administrator
~~~~~~~~~~~~~

Admins hold the highest level of all roles and permissions and have no restrictions. Administrators are able to modify settings of the underlying Drupal platform, and can modify most things of the site to meet user needs. This role is for a web professional with high technical competency and a good understanding of how Drupal works.

**Permissions:**
  - Modify themes and layouts, and enable or disable modules.
  - Modify Drupal settings

Account settings
----------------

Some settings in user management are automated to streamline the process of adding new accounts. From the Site Configuration menu, you can change default behaviors for users on the Account settings page. Default behaviors act for all accounts, so you want to make selections that should apply generally to most users.

Where to change the account settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

From the Admin Menu, click the Site Configuration menu link (not an item on the drop-down menu). This link will take you to the main Configuration page. Of the options on this page, find the People section and click on the Account Settings link.

.. figure:: ../../images/site_manager_playbook/people/people_01.png
   :alt: The "Account Settings" link under Site Configuration.

Account registration
~~~~~~~~~~~~~~~~~~~~

Decide who can register an account on your site and how with Account registration settings. On most DKAN sites, an account requires a Site Manager to create the account and login credentials for the user. But there are cases where site visitors should be able to create an account to access certain capabilities.

Who can register accounts? Choose which users register accounts from three options:

- **Administrators only:** With this option, only Site Managers are allowed to add new users to the site and assign roles. This option is best if you expect users to be from within your organization.
- **Visitors:** This option allows site visitors to create an account as soon as they fill out a profile and login to the site with their own password. Visitor accounts automatically assign the lowest-permissions role, Authenticated User. These permissions allow a user to create a profile and leave comments, but they don't have access to any of the content on the site. This option is not recommended unless you have measures like Captcha in place to protect from spamming.
- **Visitors but administrator approval is required:** With this option, site visitors can register an account but a Site Manager must first approve the account before a user can login to the site. Approval from an administrator (Site Manager) can help filter out fake accounts and give Site Managers greater control over who is accessing the site.
   Require email verification. With this option, users first have to verify their email address before they're allowed to login. Once they verify they will be prompted to change their password. This is an additional option to include in how accounts are registered. This verification can help prevent fake accounts and spamming (recommended).

Automatic email messages
~~~~~~~~~~~~~~~~~~~~~~~~

By default, DKAN comes with template responses for certain actions. You can customize these messages with your own text and by using tokens. Tokens are a way to automate certain information. Instead of typing a new username each time for a welcome message, you could simply use the users token. Click on the Browse available tokens link to see all your options.

You can also manage notifications of messages in this menu. You can optionally send a notification when certain actions are taken, but not all of these templates are automatically sent. You’ll want to review the email options to make sure the settings meet your needs.

.. figure:: ../../images/site_manager_playbook/people/people_02.png
   :alt: Screenshot of the Account Settings screen where you can modify emails sent to users.
