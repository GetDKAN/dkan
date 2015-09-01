core = 7.x
api = 2

projects[entityreference_unpublished_node][download][type] = git
projects[entityreference_unpublished_node][download][url] = "http://git.drupal.org/sandbox/Ayrmax/1977458.git"
projects[entityreference_unpublished_node][subdir] = contrib
projects[entityreference_unpublished_node][type] = module

projects[features_roles_permissions][version] = 1.0
projects[features_roles_permissions][subdir] = contrib

projects[menu_admin_per_menu][version] = 1.0
projects[menu_admin_per_menu][subdir] = contrib

projects[roleassign][version] = 1.0
projects[roleassign][subdir] = contrib

projects[role_delegation][version] = 1.1
; Forbid editing of accounts with higher permission: https://drupal.org/node/1156414
projects[role_delegation][patch][1156414] = https://drupal.org/files/issues/1156414-prevent-editing-of-certain-users-16.patch
projects[role_delegation][subdir] = contrib

projects[role_export][version] = 1.0
projects[role_export][subdir] = contrib

projects[view_unpublished][version] = 1.x-dev
projects[view_unpublished][subdir] = contrib

projects[workbench][version] = 1.2
projects[workbench][subdir] = contrib

projects[workbench_moderation][version] = 1.4
projects[workbench_moderation][subdir] = contrib
projects[workbench_moderation][patch][2393771] = https://www.drupal.org/files/issues/specify_change_state_user-2393771-5.patch

projects[workbench_email][version] = 3.3
projects[workbench_email][subdir] = contrib
projects[workbench_email][patch][2391233] = https://www.drupal.org/files/issues/workbench_email-2391233-3.patch
projects[workbench_email][patch][2529016] = https://www.drupal.org/files/issues/workbench_email-skip_filter_anonymous-2529016.patch

projects[menu_badges][version] = 1.2
projects[menu_badges][subdir] = contrib

projects[link_badges][version] = 1.1
projects[link_badges][subdir] = contrib
