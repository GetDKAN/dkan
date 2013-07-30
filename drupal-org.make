core = 7.x
api = 2

; DKAN
projects[dkan_dataset][subdir] = dkan
projects[dkan_dataset][download][type] = git
projects[dkan_dataset][download][url] = "http://git.drupal.org/project/dkan_dataset.git"
projects[dkan_dataset][type] = "module"

projects[dkan_datastore][subdir] = dkan
projects[dkan_datastore][download][type] = git
projects[dkan_datastore][download][url] = "http://git.drupal.org/project/dkan_datastore.git"
projects[dkan_datastore][type] = "module"

; Contrib modules, for dkan_sitewide. May move to own module.
projects[admin_menu][subdir] = contrib
projects[bueditor][subdir] = contrib
projects[bueditor][patch][1931862] = http://drupal.org/files/dont-render-bueditor-for-plain-text-textareas.patch
projects[context][subdir] = contrib
projects[delta][subdir] = contrib
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][version] = 1.x
projects[defaultcontent][patch][1436094] = http://drupal.org/files/node_sort_value-1436094-6.patch
projects[diff][subdir] = contrib
projects[facetapi][subdir] = contrib
projects[facetapi_pretty_paths][subdir] = contrib
projects[gravatar][subdir] = contrib
projects[gravatar][version] = 1.x
projects[gravatar][patch][1568162] = http://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch
projects[markdowneditor][subdir] = contrib
projects[markdowneditor][version] = 1.x
projects[markdowneditor][patch][2045225] = http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch
projects[markdown][subdir] = contrib
projects[pathauto][subdir] = contrib
projects[r4032login][subdir] = contrib
projects[search_api][version] = 1.6
projects[search_api][subdir] = contrib
projects[search_api_ajax][subdir] = contrib
projects[search_api_ajax][patch][1858530] = http://drupal.org/files/search_api_ajax-search-query.patch
projects[search_api_ajax][patch][1719556] = http://drupal.org/files/links-in-search-results-clickable-1719556-4.patch
projects[search_api_db][subdir] = contrib
projects[rules][subdir] = contrib

libraries[font_awesome][type] = libraries
libraries[font_awesome][download][type] = git
libraries[font_awesome][download][url] = "https://github.com/FortAwesome/Font-Awesome.git"
libraries[font_awesome][directory_name] = font_awesome
libraries[font_awesome][download][revision] = "13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09"

projects[omega][version] = 3.1
projects[omega][subdir] = contrib
projects[omega][patch][1828552] = http://drupal.org/files/1828552-omega-hook_views_mini_pager.patch
