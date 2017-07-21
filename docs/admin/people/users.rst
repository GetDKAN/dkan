DKAN User Management
====================

.. warning::

  This was a document that existed before we did the migration from Insights. This should be reviewed and merged into the People section.

DKAN uses Drupal's build-in, powerful user account system, with some customizations captured in the DKAN Sitewide User module. Flexible combinations of permissions, restrictions, and authorization schema mean that you can design an open data system that meets the needs of all of your users -- from visitors to data publishers to developers -- securely and easily.

.. figure:: ../images/site_manager_playbook/managing_users/managing_users_01.png
   :alt: The People page, where you can manage users.

Managing, Editing, and Deleting Existing Users
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Adding a new user
-----------------

* Log in to your site as the administrative super-user. Note the thin black administration menu that appears at the top of each page once you’ve logged in. This “admin menu” contains shortcuts to all administrative tasks.
* Visit your site’s User Management page by clicking “People” in the admin menu.</li>
* Select the “+ Add user” option at the top of the user management page to add a new user.</li>
* Complete the “add user” form as shown, adding the user to any roles such as “editor” as appropriate. Note the “notify user” option which will send an email with initial login instructions to the user’s email address.
* Click the “Create new account” button when complete.

Managing existing users
-----------------------

After creating an account, you can continue to manage users’ profiles from the People menu item in the Admin Menu. From this screen you can see all the existing users, their roles, and details about their account, and by clicking on individual users you can additionally see all the content the user has created. You can also edit their account to change details, add or remove a role, add them to Groups or cancel an account.

**To edit an existing user's account:**

* Visit your site’s User Management page by clicking “People” in the admin menu.
* The displayed list of users on the User Management page can be filtered and sorted using the filters at the top of the page. Once you’ve found the user you wish to edit in the user table, click the “edit” link at the end of that user’s row.
* On the resulting “edit user” page, you can edit the user’s username, email, or profile information. You can also set a new password for the user. Click the “Save” button at the bottom of the page to save your changes.
* Use the “Cancel account” at the bottom of the edit user page to delete an account. You will be given the option to preserve or delete any website content added by that user before deletion.

Finding users and bulk actions
------------------------------

On some sites, the list of users can be several pages long. To find a specific user or group of users you can use the filters to narrow the results.

Filter users and refine results. Filters are most helpful when you're looking for a group of users that can meet a general search criteria. This makes it easy to manage multiple users at the same time or find an individual user without needing to browse through several pages of users. There are two types of filters to narrow users: role and status.

- **Role.** Filtering users by a role is best used for managing users by permissions. You may want to give a group of users higher permissions or you may want to change the status of a group of users. By selecting a role, the results will only show users that are assigned that specific role.
- **Status.** You can also filter users with a certain status. Click the drop-down menu in the status field and click active to show only active users. Showing only active users ignores users that are not active so that you manage only relevant accounts.

**Refine results.** If your search is more complex, you can refine the results of the initial results by adding additional search criteria. You can select multiple roles and/or status. So the results would only show users who meet all the criteria.

.. figure:: ../images/site_manager_playbook/managing_users/managing_users_02.png
   :alt: A screencap of the "Show Only Users Where" screen for narrowing down specific users.

Select the first criteria and click the Filter button to narrow the user list. Once the results are narrowed, you can select an additional search criteria and click the Refine button to narrow the results further. You can do this for multiple search criteria.

Click the Undo button to remove the last search criteria you added or click the Reset button to remove all the search criteria. Make sure to click the Reset button once you're done with your search. Otherwise the results will remain narrowed, even if you navigate to another page, until all the criteria have been removed.

.. figure:: ../images/site_manager_playbook/managing_users/managing_users_03.gif
   :alt: An animated screencap showing user management on the People page.

**Bulk actions.** When you have several users that require the same action, you can use this menu to perform bulk actions on a group of users. Rather than spend extra time performing the same action over and over again for individual users, you can select multiple users and make the change for the group with just one click.

From the People page, select all the user accounts that need to be updated by checking the white check box next to their username. Find the drop-down Update options menu and choose the action you want to perform. You can quickly make changes for a group of users like adding or removing a role and blocking or canceling their accounts.

In the example below, the Site Manager first uses the status filter to show only blocked users. Then the Site Manager checks the users that should be unblocked, and then finds the Unblock the selected user option in the Update options drop-down menu. Finally, they click the Update button to finalize the changes.

.. figure:: ../images/site_manager_playbook/managing_users/managing_users_04.gif
   :alt: An animated screencap showing user management on the People page.

Blocking a user or canceling an account
---------------------------------------

At some point, most user accounts will need to be deleted or blocked. Typically this is for internal employees who move on from the organization, but there are occasions involving external users. There are a number of options for canceling an account or blocking a user to meet a number of scenarios.

**Block an account.** Blocking an account is the most simple and straightforward way to suspend an account. Blocking a user account keeps a user from logging in, and accounts can easily be unblocked. A blocked account only means that a user cannot login to their account and access your DKAN site. All of their content and profile details will remain, so nothing is lost if you want to unblock an account and restore access.

For users accounts belonging to your organization, blocking an account is typically a temporary action. For user accounts that belong to people who may have registered the account themselves, blocking is likely to be more permanent. By blocking an account, you keep users from creating a new account with the same details and avoid repeating the blocking process.

You can block a single user by editing their profile and changing their status, or you can block several users at once by using the bulk actions function on the People page. In the example below, the Site Manager is blocking a user account by editing an individual user profile. To finalize the changes, the Site Manager must click the Save button at the bottom of the page.

.. figure:: ../images/site_manager_playbook/managing_users/managing_users_05.gif
   :alt: An animated screencap showing how to block unwanted users on the People page.

**Cancel an account.** Canceling an account can be a permanent action, and there are several options to choose from. Some of the actions cannot be reversed, so you should be careful when deciding which option to choose. Below are the options for canceling an account and the implications of selecting the option. While Site Managers can cancel the account of any user on the site, users may also cancel their own accounts.

.. figure:: ../images/site_manager_playbook/managing_users/managing_users_06.png
   :alt: An image displaying what happens during the process of canceling a user's account.

- **Disable the account and keep its contents:** If you disable the account, the details of the profile remain in tact but the user is blocked from accessing the site with their user login. By keeping the contents, any content that the user published will remain on the live site. Because the account is only disabled (blocked) the user remains as the author of the content and the profile details may still be accessed. This option is similar to just blocking an account, and it's a good temporary measure in most cases.
- **Disable the account and unpublish its contents:** This option blocks the user from accessing the site and all the content that the user has published will be unpublished. This means that their content will not appear on the live site, but it will still exist behind the scenes. It can be managed out of public view and in the mean time, the user cannot do anything else on the site. This is a good option if you need to review the content a user has published and need it to be off the site but still need to access it.
- **Delete the account and make its contents belong to the Anonymous User:** This is a permanent action. Once you delete an account, you cannot recover any of the details that were associated with the user profile. With this option you can delete the entire account as well as keep its contents. Because the account associated with the user who was the original author no longer exists, the content must be assigned to a different author. This option quickly changes the author so that the content remains on the live site, and you can change the author at any time. Again, this is a permanent option so be careful before making this selection.
- **Delete the account and its contents:** This is a permanent action and the most severe choice when canceling an account. This options not only deletes the user account and all the profile details, it also deletes all the content the user added. Neither the account nor the content can be recovered with this selection. As a general best practice, we recommend never deleting content if it can be edited or simply unpublished.

**Require email confirmation:** For any option you choose when canceling an account, you can make sure the user is aware by requiring email confirmation. An email will be sent to the email address provided in the user's profile details. When you check the Require email confirmation box, the account won't be canceled until the user confirms through the email.
