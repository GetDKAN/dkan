core = 7.x
api = 2

; DKAN
projects[dkan_dataset][subdir] = dkan
projects[dkan_dataset][download][type] = git
projects[dkan_dataset][download][url] = git@github.com:NuCivic/dkan_dataset.git
projects[dkan_dataset][download][branch] = 7.x-1.x

projects[dkan_datastore][subdir] = dkan
projects[dkan_datastore][download][type] = git
projects[dkan_datastore][download][url] = git@github.com:NuCivic/dkan_datastore.git
projects[dkan_datastore][download][branch] = 7.x-1.x

includes[dkan_dataset_make] = https://raw.githubusercontent.com/NuCivic/dkan_dataset/7.x-1.x/dkan_dataset.make
includes[dkan_datastore_make] = https://raw.githubusercontent.com/NuCivic/dkan_datastore/7.x-1.x/dkan_datastore.make

; Contrib Modules
projects[colorizer][version] = 1.0
projects[colorizer][patch][2227651] = http://drupal.org/files/issues/colorizer-2227651.patch
projects[colorizer][subdir] = contrib

projects[diff][version] = 3.2
projects[diff][subdir] = contrib

projects[og_moderation][version] = 2.2
projects[og_moderation][patch][2231737] = https://drupal.org/files/issues/any-user-with-view-revision-can-revert-delete-2231737-1.patch
projects[og_moderation][subdir] = contrib

projects[restws][version] = 2.1
projects[restws][subdir] = contrib

projects[views_bulk_operations][version] = 3.2
projects[views_bulk_operations][subdir] = contrib

projects[views_responsive_grid][version] = 1.3
projects[views_responsive_grid][subdir] = contrib

projects[schema][version] = 1.2
projects[schema][patch][1237974] = http://drupal.org/files/schema-support-custom-types-1237974-48.patch
projects[schema][subdir] = contrib

projects[admin_menu][version] = 3.0-rc4
projects[admin_menu][subdir] = contrib

projects[bueditor][version] = 1.7
projects[bueditor][patch][1931862] = http://drupal.org/files/dont-render-bueditor-for-plain-text-textareas.patch
projects[bueditor][subdir] = contrib

projects[delta][version] = 3.0-beta11
projects[delta][subdir] = contrib

projects[markdowneditor][version] = 1.2
projects[markdowneditor][patch][2045225] = http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch
projects[markdowneditor][subdir] = contrib

projects[markdown][version] = 1.2
projects[markdown][subdir] = contrib

projects[pathauto][version] = 1.2
projects[pathauto][subdir] = contrib

projects[r4032login][version] = 1.7
projects[r4032login][subdir] = contrib

projects[rules][version] = 2.3
projects[rules][subdir] = contrib

projects[imagecache_actions][download][type] = git
projects[imagecache_actions][download][url] = "http://git.drupal.org/project/imagecache_actions.git"
projects[imagecache_actions][download][branch] = 7.x-1.x
projects[imagecache_actions][download][revision] = cd19d2a
projects[imagecache_actions][subdir] = contrib
projects[imagecache_actions][type] = module

; Themes
projects[omega][version] = 3.1
projects[omega][patch][1828552] = http://drupal.org/files/1828552-omega-hook_views_mini_pager.patch
projects[omega][subdir] = contrib

projects[bootstrap][downloald][version] = 3.x
projects[bootstrap][downloald][type] = git
projects[bootstrap][downloald][revision] = "0390173732439fd60e898c7086219ab8c99c2f3d"
projects[bootstrap][subdir] = contrib

projects[dkan_dataset][subdir] = dkan
projects[dkan_dataset][download][type] = git
projects[dkan_dataset][download][url] = git@github.com:NuCivic/nuboot.git
projects[dkan_dataset][download][branch] = 7.x-1.x

; Libraries
libraries[font_awesome][type] = libraries
libraries[font_awesome][download][type] = git
libraries[font_awesome][download][url] = "https://github.com/FortAwesome/Font-Awesome.git"
libraries[font_awesome][directory_name] = font_awesome
libraries[font_awesome][download][revision] = "13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09"

libraries[spyc][download][type] = "get"
libraries[spyc][download][url] = "https://raw.github.com/mustangostang/spyc/79f61969f63ee77e0d9460bc254a27a671b445f3/spyc.php"
libraries[spyc][filename] = "../spyc.php"
libraries[spyc][directory_name] = "lib"
libraries[spyc][destination] = "modules/contrib/services/servers/rest_server"
