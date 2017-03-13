core = 7.x
api = 2

projects[taxonomy_fixtures][download][type] = git
projects[taxonomy_fixtures][download][url] = https://github.com/NuCivic/taxonomy_fixtures.git
projects[taxonomy_fixtures][download][branch] = 7.x-1.x

projects[font_icon_select][download][type] = git
projects[font_icon_select][download][url] = https://git.drupal.org/sandbox/wolffereast/2319993.git
projects[font_icon_select][download][branch] = 7.x-1.x

projects[conditional_fields][version] = 3.0-alpha2
projects[taxonomy_menu][version] = 1.5
projects[color_field][version] = 1.8
projects[color_field][patch][2696505] = https://www.drupal.org/files/issues/color_field-requirements-2696505-v2.patch
projects[entity_path][version] = 1.x-dev
projects[entity_path][patch][2809655] = https://www.drupal.org/files/issues/entity-path-mysql-5-7_3.diff
projects[globalredirect][version] = 1.5

; Libraries
libraries[spectrum][type] = libraries
libraries[spectrum][download][type] = git
libraries[spectrum][download][url] = https://github.com/bgrins/spectrum.git
libraries[spectrum][destination] = libraries
libraries[spectrum][directory_name] = bgrins-spectrum
