DKAN Roles and Permissions
===========================
Roles categorize types of users. Permissions are assigned to these roles and represent functions the user can perform across the site.

Below is a list of the roles and general permissions included in the **DKAN Permissions** module. The descriptions should help show what different user types are able to do on the site. Whan adding new users, site managers will assign the appropriate role(s) to match the tasks the user is expected to perform.

There will also be cases where you need users to have different permissions in the context of a particular **group** (for instance, to be able to modify content belonging to their agency but not other agencies on the same website). Read more about `group permissions here <dataset/groups.html#roles-and-permissions>`_.

The DKAN Permissions module
---------------------------
The DKAN Permissions module provides default roles and permissions for the DKAN distribution. It uses the export method provided by the `Features Roles Permissions <https://www.drupal.org/project/features_roles_permissions>`_ module so you can examine the specific roles and permissions provided by reviewing `dkan_permissions.features.roles_permissions.inc <https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_permissions/dkan_permissions.features.roles_permissions.inc>`_.

Drupal Core Roles
---------------------------

Anonymous User
^^^^^^^^^^^^^^
* General site visitors that are **not** logged in.
* Can view and search published content.

Authenticated User
^^^^^^^^^^^^^^^^^^^
These users have an account on the site, can log in, and have profile information that can be verified (and authenticated). This type has the **lowest** level of permissions.

**Permissions:**

* Can log in.
* Can edit their user profile.
* Can view and search published content.

Administrator
^^^^^^^^^^^^^^^
The administrator role holds **every** permission and requires high technical competency. This role has the ability to cause serious damage, so it’s generally reserved for a single web professional. Administrators hold the **highest level** of all roles and permissions.

**Permissions:**

* Enable/disable modules and features.
* Change the appearance of the site with alternate themes.
* Create and edit user roles and permissions.
* Create views, blocks, features, content types.
* Access any UI configuration.

DKAN Core Roles
---------------------------

Content Creator
^^^^^^^^^^^^^^^
Content Creators can add content to the site. This will be someone working in your organization who helps by adding to the data catalogue but is not responsible for anything more. This level of access takes users into the production side of the site, but gives little freedom to move outside of creating and adding certain content types. Limiting this role is critical for avoiding inadvertent damage to site content.

**Permissions:**

* Create dataset, resource, data story, and data dashboard content.
* Create chart visualizations.
* Edit **own** content (can not edit content added by another user).
* View **own** unpublished content and revision history of all published content.
* Add and view files to site library.

Editor
^^^^^^^^^^^^^^^
This will typically be a person handling the content on a frequent basis. Someone in your organization with expertise on the subject-matter that is expansive as well as in-depth. An editor role is similar to a content creator role because the focus is on content, however, an editor will have the ability to manage and edit content added by others. The editor role does not go further into administrative functions.

**Permissions:**

* Create page, dataset, resource, data story, and data dashboard content.
* Create chart visualizations.
* Edit, delete, and manage versions of content added by other users.
* Add and view files to site library.

Site Manager
^^^^^^^^^^^^^^^
The highest level for *non-technical* users. A site manager is mostly concerned with the admin functions of the site. Typically this will fall to someone in a supervisory role. The site manager takes a high-level view of the site, its content, and the users on the site. This person is able to make general configurations to the site and assigns roles to new users but does not deal with the technical configuration of the backend.

**Permissions:**

* Create, edit, delete **all** content types created by **any** user.
* Create, edit and manage **Harvest Source** content.
* Assigns roles to all user levels, but cannot create new roles/perms.
* Create and manage groups.
* Manage site logo, name, slogan, copyright, colors, fonts, main menu, recline config, open data schema mapper, dataset forms and previews.

DKAN Workflow Roles
--------------------
If your organizaton needs an editorial workflow for managing content creation and editing, DKAN also includes a feature called DKAN Workflow that adds three more roles to establish a content approval process. `Read more about that here <workflow.html#workflow-roles>`_.

Installation/upgrade notes
--------------------------
On new DKAN installs, the DKAN Permissions module is enabled by default. The older ("sidewide"-namespaced) permissions module will still be included in the codebase to support existing sites, but will be disabled by default on new installs. For _existing_ sites, the opposite is true - updating your code will _not_ cause the newer module to be enabled automatically or disable the older permissions module.

If you have been using the older DKAN Sitewide Roles and Permissions module on an existing site and do upgrade, we do recommend you disable it and enable the new DKAN Permissions module. The newer module has an improved set of roles and permissions designed around what we consider the most general use-cases. However, this will likely mean reviewing all of the user accounts on your site and ensuring that they have the roles that they should.

You also of course have the option of disabling both modules, setting your own prefered roles and permissions and exporting those to a custom feature.
