# DKAN Permissions

This module provides default roles and permissions for the DKAN distribution. It uses the alternative [Features](https://www.drupal.org/project/features) export method provided by the [Features Roles Permissions](https://www.drupal.org/project/features_roles_permissions) module, rather than Features' standard permissions and roles exports. Among other advantages, this produces very human-readable code; you can examine the specific roles and permissions provided by reviewing [`dkan_permissions.features.roles_permissions.inc`](https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_permissions/dkan_permissions.features.roles_permissions.inc).

## Installation/upgrade notes

On new DKAN installs, this module is enabled by default. The older ("sidewide"-namespaced) permissions module will still be included in the codebase to support existing sites, but will be disabled by default on new installs. For _existing_ sites, the opposite is true - updating your code will _not_ cause the newer module to be enabled automatically or disable the older permissions module.

If you have been using the older DKAN Sitewide Roles and Permissions module on an existing site and do upgrade, we do recommend you disable it and enable the new DKAN Permissions module. The newer module has a better-thought-out set of roles and permissions designed around what we consider the most general use-cases. However, this will likely mean reviewing all of the user accounts on your site and ensuring that they have the roles that they should. 

You also of course have the option of disabling both modules, setting your own prefered roles and permissions and exporting those to a custom feature.

For detailed documentation of the DKAN Permissions module, see [our blog post](http://www.nucivic.com/dkan-roles-and-permissions-just-got-easier-in-the-latest-release/).

## Group Permissions

Group-level permissions are now defined in the [DKAN Dataset Groups Permissions](https://github.com/NuCivic/dkan_dataset/tree/7.x-1.x/modules/dkan_dataset_groups/modules/dkan_dataset_groups_perms) (dkan_dataset_groups_perms) module, part of the [DKAN Dataset](https://github.com/NuCivic/dkan_dataset) project.
