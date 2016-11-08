# DKAN Roles and Permissions

## The DKAN Permissions module

The DKAN Permissions module provides default roles and permissions for the DKAN distribution. It uses the alternative [Features](https://www.drupal.org/project/features) export method provided by the [Features Roles Permissions](https://www.drupal.org/project/features_roles_permissions) module, rather than Features' standard permissions and roles exports. Among other advantages, this produces very human-readable code; you can examine the specific roles and permissions provided by reviewing [`dkan_permissions.features.roles_permissions.inc`](https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_permissions/dkan_permissions.features.roles_permissions.inc).

## DKAN Core Roles

Your website is a great resource for your stakeholders to see the hard work of your organization as well as access important, relevant information. Site visitors should be able to easily navigate the site to find what they are looking for as well as learn about your efforts. The balance between providing thorough information while promoting the most timely and pertinent is an ongoing consideration in managing web content. It takes a team of people working together by playing individual roles that contribute to a larger effort to have a successful site.

On a website, roles categorize types of users and the associated permissions determine what that user type is allowed to do and see behind the scenes. It’s the site administrator who sets up a site initially. This role generally falls to a highly technical user, and site administrators have every permission possible. But this person won’t necessarily be involved in the daily management of a website. Another person will handle that management piece including adding new users as there are additions and subtractions from the team.

Core roles are associated with permissions and those permissions represent functions the users can perform across the site. These permissions are user-level, meaning the role is set in the user’s specific record.

Below is a detailed description of roles and the permissions included in the DKAN Permissions module to help show what different user types are able to do on the site and help site managers decide who should have what role. A few of these roles won’t affect the day-to-day of managing the website or the content. Larger text is used to emphasize the more relevant roles.

Naturally users will need more than one role to fulfill the full scope of their job. There will also be cases where you need users to have different permissions in the context of a particular group (for instance, to be able to modify content belonging to their agency but not other agencies on the same website). Keep reading after the table to find out more about group permissions.

A note about the new roles: If you are already operating on an older version of DKAN, updating won’t automatically enable this module or migrate your users over to these new roles. We recommend disabling the old “DKAN Sitewide Roles and Permissions” module and enabling the new “DKAN Permissions” module, but be aware that you will probably need to review all of your site’s users to make sure they have the appropriate role going forward.


### Core Roles

#### Anonymous User

* General site visitors that are **not** logged in. 
* This user type has no profile information, hence it’s anonymous. 
* Can view and search content on public website.

#### Authenticated User

These users are logged on and have profile information that can be verified (and authenticated). This type has the **lowest** level of permissions; all users with login credentials have this role. This user type is a general site visitor but he or she has created an account.

Permissions:

* Have a profile and add/edit that profile.
* Can leave comments on the data catalog, if comments are enabled.
* DKAN can verify account information with a created profile so that the user is able to take limited actions and manage profile details.

#### Content Creator

Content Creators are focused simply on adding resources to the data catalogue under direction of a supervisor. At this level, the role **must** be assigned by a higher role; this role has access to the production side of the site. This will be someone working in your organization who helps by adding to the data catalogue but doesn’t need to worry about the whole site. This level of access takes users into the production side of the site, but gives little freedom to move outside of creating and adding certain content types. Limiting this role is critical for avoiding inadvertent damage to site content.

Permissions:

* Create most content types and edit **own** content. 
* View **own** unpublished content and revision history of all published content. 
* Add and view files to site library.

#### Editor

This will typically be a person handling the content on a frequent basis. Someone in your organization with expertise on the subject-matter that is expansive as well as in-depth. | An editor role is similar to a content creator role because the focus is content, however an editor will deal with multiple content authors and have the ability to manage and edit. An editor is responsible for managing content from a strategic perspective.  The editor largely handles the quality and timeliness of the content that appears on the site. This role is able to make changes to content and where it appears, but it doesn’t go further into admin functions.

Permissions: 

* Assign roles to lower-level users.
* View, edit, delete most content types and manage versions of content.
* Access and manage DKAN datastore

#### Site Manager

The highest level for *non-technical* users. A site manager is mostly concerned with admin functions of the site. Typically this will fall to someone in a supervisory role. The site manager takes a high-level view of the site, its content, and the users on the site. This person maintains the site and assigns roles and permissions to new users but doesn’t deal with the technical backend. 

Permissions: 

* Create, edit, delete **all** content types created by **any** user.
* Change the types of content that lower-access roles can create.
* Assigns roles to all user levels, but cannot create new roles/perms.
* Create and manage groups.

#### Administrator

The administrator role holds every permission, and it requires high technical competency. This role has the ability to cause serious damage, so it’s generally reserved for a single web professional. The administrator handles the overall structure of the website for lower-access roles to plug content into and make changes as needed. Admins hold the **highest level** of all roles and permissions. 

Permissions: 

* Enable/disable DKAN modules and features.
* Change the appearance of the site with views and themes.
* Create and edit  user roles and permissions.

## Installation/upgrade notes

On new DKAN installs, the DKAN Permissions module is enabled by default. The older ("sidewide"-namespaced) permissions module will still be included in the codebase to support existing sites, but will be disabled by default on new installs. For _existing_ sites, the opposite is true - updating your code will _not_ cause the newer module to be enabled automatically or disable the older permissions module.

If you have been using the older DKAN Sitewide Roles and Permissions module on an existing site and do upgrade, we do recommend you disable it and enable the new DKAN Permissions module. The newer module has a better-thought-out set of roles and permissions designed around what we consider the most general use-cases. However, this will likely mean reviewing all of the user accounts on your site and ensuring that they have the roles that they should.

You also of course have the option of disabling both modules, setting your own prefered roles and permissions and exporting those to a custom feature.
