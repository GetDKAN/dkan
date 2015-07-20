core = 7.x
api = 2

; Set the default subdirectory for projects.
defaults[projects][subdir] = contrib

; DKAN
projects[dkan_dataset][subdir] = dkan
projects[dkan_dataset][download][type] = git
projects[dkan_dataset][download][url] = https://github.com/NuCivic/dkan_dataset.git
projects[dkan_dataset][download][branch] = 7.x-1.x

projects[dkan_datastore][subdir] = dkan
projects[dkan_datastore][download][type] = git
projects[dkan_datastore][download][url] = https://github.com/NuCivic/dkan_datastore.git
projects[dkan_datastore][download][branch] = 7.x-1.x

includes[dkan_dataset_make] = https://raw.githubusercontent.com/NuCivic/dkan_dataset/570_jquery_vbo_update/dkan_dataset.make
includes[dkan_datastore_make] = https://raw.githubusercontent.com/NuCivic/dkan_datastore/7.x-1.x/dkan_datastore.make

projects[file_entity][patch][2308737] = https://www.drupal.org/files/issues/file_entity-remove-field-status-check-2308737-9509141.patch

; Contrib Modules
projects[admin_menu][version] = 3.0-rc5

projects[bueditor][version] = 1.7
projects[bueditor][patch][1931862] = http://drupal.org/files/dont-render-bueditor-for-plain-text-textareas.patch

projects[colorizer][version] = 1.4
projects[colorizer][patch][2227651] = https://www.drupal.org/files/issues/colorizer-add-rgb-vars-2227651-4b.patch
projects[colorizer][patch][2444249] = https://www.drupal.org/files/issues/colorizer-2444249.patch

projects[conditional_styles][version] = 2.2

projects[diff][version] = 3.2

projects[draggableviews][version] = 2.1

projects[entityreference_filter][version] = 1.5

projects[fieldable_panels_panes][version] = 1.6

projects[honeypot][version] = 1.17

projects[fontyourface][version] = 2.8

projects[imagecache_actions][download][type] = git
projects[imagecache_actions][download][url] = "http://git.drupal.org/project/imagecache_actions.git"
projects[imagecache_actions][download][branch] = 7.x-1.x
projects[imagecache_actions][download][revision] = cd19d2a
projects[imagecache_actions][type] = module

projects[markdown][version] = 1.2

projects[markdowneditor][version] = 1.2
projects[markdowneditor][patch][2045225] = http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch

projects[module_filter][version] = 2.0

projects[og_moderation][version] = 2.2
projects[og_moderation][patch][2231737] = https://drupal.org/files/issues/any-user-with-view-revision-can-revert-delete-2231737-1.patch

projects[defaultconfig][version] = 1.0-alpha9

projects[panelizer][version] = 3.1

projects[views_autocomplete_filters][version] = 1.1
projects[views_autocomplete_filters][patch][2277453] = http://drupal.org/files/issues/ViewsAutocompleteFilters-no_results_on_some_environments-2277453-1.patch
projects[views_autocomplete_filters][patch][2374709] = http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
projects[views_autocomplete_filters][patch][2317351] = http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch

projects[panopoly_widgets][version] = 1.21
projects[panopoly_widgets][patch][] = patches/panopoly_widgets_overrides.patch
includes[panopoly_widgets_make] = http://cgit.drupalcode.org/panopoly_widgets/plain/panopoly_widgets.make

projects[panopoly_images][version] = 1.21
includes[panopoly_images_make] = http://cgit.drupalcode.org/panopoly_images/plain/panopoly_images.make

projects[panels][version] = 3.5

projects[path_breadcrumbs][version] = 3.2

projects[pathauto][version] = 1.2

projects[radix_layouts][version] = 3.3

projects[r4032login][version] = 1.7

projects[rules][version] = 2.3

projects[restws][version] = 2.3

projects[schema][version] = 1.2

; Deprecated
projects[delta][version] = 3.0-beta11

; Themes
projects[omega][version] = 3.1
projects[omega][patch][1828552] = http://drupal.org/files/1828552-omega-hook_views_mini_pager.patch

;projects[bootstrap][download][version] = 3.x
;projects[bootstrap][download][type] = git
;projects[bootstrap][download][revision] = "0390173732439fd60e898c7086219ab8c99c2f3d"

;projects[nuboot][download][type] = git
;projects[nuboot][download][url] = https://github.com/NuCivic/nuboot.git
;projects[nuboot][download][revision] = "fbd7ea2c2f1fa45a5f5a10b4215950940335879e"
;projects[nuboot][download][branch] = 7.x-1.x

projects[nuboot_radix][download][type] = git
projects[nuboot_radix][download][url] = https://github.com/NuCivic/nuboot_radix.git
projects[nuboot_radix][download][branch] = IE-Updates
projects[nuboot_radix][download][revision] = "21f2fa7a408e16eeb5638a3d550674037a234497"
projects[nuboot_radix][type] = theme

projects[radix][type] = theme

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

