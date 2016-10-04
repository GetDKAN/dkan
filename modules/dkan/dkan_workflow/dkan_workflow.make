core = 7.x
api = 2

projects[workbench][version] = 1.2
projects[workbench][subdir] = contrib

projects[workbench_moderation][download][type] = git
projects[workbench_moderation][download][branch] = 7.x-1.x
projects[workbench_moderation][download][revision] = "2c91211"
projects[workbench_moderation][subdir] = contrib
projects[workbench_moderation][patch][2393771] = https://www.drupal.org/files/issues/specify_change_state_user-2393771-5.patch
projects[workbench_moderation][patch][1838640] = https://www.drupal.org/files/issues/workbench_moderation-fix_callback_argument-1838640-23.patch

projects[workbench_email][version] = 3.9
projects[workbench_email][subdir] = contrib
projects[workbench_email][patch][2391233] = https://www.drupal.org/files/issues/workbench_email-2391233-3.patch

projects[menu_badges][version] = 1.2
projects[menu_badges][subdir] = contrib

projects[link_badges][version] = 1.1
projects[link_badges][subdir] = contrib
