core = 7.x
api = 2

;--------------------
; Contrib
;--------------------

projects[admin_menu][subdir] = contrib
projects[autocomplete_deluxe][subdir] = contrib
projects[boxes][subdir] = contrib
projects[chosen][subdir] = contrib
projects[chosen][version] = 2.x
projects[context][subdir] = contrib
projects[ctools][subdir] = contrib
projects[ctools][version] = 1.x
projects[delta][subdir] = contrib
projects[defaultcontent][subdir] = contrib
projects[diff][subdir] = contrib
projects[double_field][subdir] = contrib
projects[entity][subdir] = contrib
projects[entityreference][subdir] = contrib
projects[eva][subdir] = contrib
projects[facetapi][subdir] = contrib
projects[facetapi_pretty_paths][subdir] = contrib
projects[features][subdir] = contrib
projects[field_group][subdir] = contrib
projects[gravatar][subdir] = contrib
projects[jquery_update][subdir] = contrib
projects[libraries][subdir] = contrib
projects[link][subdir] = contrib
projects[link_iframe_formatter][subdir] = contrib
projects[r4032login][subdir] = contrib
projects[multistep][subdir] = contrib
projects[og][subdir] = contrib
projects[og][version] = 2.x
projects[og_extras][subdir] = contrib
projects[rules][subdir] = contrib
projects[select_or_other][subdir] = contrib
projects[search_api][subdir] = contrib
projects[search_api_ajax][subdir] = contrib
projects[search_api_ajax][patch][1858530] = http://drupal.org/files/search_api_ajax-search-query.patch
projects[search_api_db][subdir] = contrib
projects[strongarm][subdir] = contrib
projects[views][subdir] = contrib
projects[views_bulk_operations][subdir] = contrib

;--------------------
; Themes
;--------------------

projects[omega][version] = 3.1
projects[omega][version] = contrib

;--------------------
; Open Data
;--------------------

projects[recline][subdir] = contrib

;--------------------
; Libraries
;--------------------

libraries[recline][type] = libraries
libraries[recline][download][type] = git
libraries[recline][download][branch] = master
libraries[recline][download][url] = "git://github.com/acouch/recline.git"

libraries[font_awesome][type] = libraries
libraries[font_awesome][download][type] = git
libraries[font_awesome][download][url] = "https://github.com/FortAwesome/Font-Awesome.git"
libraries[font_awesome][directory_name] = font_awesome

libraries[chosen][type] = libraries
libraries[chosen][download][type] = git
libraries[chosen][download][url] = "https://github.com/harvesthq/chosen.git"
libraries[chosen][directory_name] = chosen

libraries[slugify][type] = libraries
libraries[slugify][download][type] = git
libraries[slugify][download][url] = "git://github.com/pmcelhaney/jQuery-Slugify-Plugin.git"
libraries[slugify][directory_name] = slugify
;--------------------
; Development
;--------------------

projects[coder][subdir] = contrib
projects[devel][subdir] = contrib
projects[omega_tools][subdir] = contrib
