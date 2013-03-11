core = 7.x
api = 2

;--------------------
; Contrib
;--------------------

projects[admin_menu][subdir] = contrib
projects[autocomplete_deluxe][subdir] = contrib
projects[autocomplete_deluxe][version] = 2.x
projects[boxes][subdir] = contrib
projects[bueditor][subdir] = contrib
projects[beautytips][subdir] = contrib
projects[chosen][subdir] = contrib
projects[chosen][version] = 2.0-alpha1
projects[context][subdir] = contrib
projects[ctools][subdir] = contrib
projects[ctools][version] = 1.x
projects[date][subdir] = contrib
projects[delta][subdir] = contrib
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][version] = 1.x
projects[defaultcontent][patch][1436094] = http://drupal.org/files/node_sort_value-1436094-3.patch
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
projects[gravatar][version] = 1.x
projects[gravatar][patch][1568162] = http://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch
projects[jquery_update][subdir] = contrib
projects[libraries][subdir] = contrib
projects[link][subdir] = contrib
projects[link_iframe_formatter][subdir] = contrib
projects[r4032login][subdir] = contrib
projects[markdowneditor][subdir] = contrib
projects[markdown][subdir] = contrib
projects[pathauto][subdir] = contrib
projects[multistep][subdir] = contrib
projects[og][subdir] = contrib
projects[og][version] = 2.x
projects[og_extras][subdir] = contrib
projects[og_extras][version] = 1.x
projects[rules][subdir] = contrib
projects[schema][subdir] = contrib
projects[select_or_other][subdir] = contrib
projects[search_api][subdir] = contrib
projects[search_api_ajax][subdir] = contrib
projects[search_api_ajax][patch][1858530] = http://drupal.org/files/search_api_ajax-search-query.patch
projects[search_api_ajax][patch][1719556] = http://drupal.org/files/links-in-search-results-clickable-1719556-4.patch
projects[search_api_db][subdir] = contrib
projects[search_api_db][patch][1346846] = http://drupal.org/files/fix-indexing-of-removed-fields-1346846-16.patch
projects[strongarm][subdir] = contrib
projects[services][subdir] = contrib
projects[token][subdir] = contrib
projects[uuid][subdir] = contrib
projects[uuid][version] = 1.x
projects[views][subdir] = contrib
projects[views_bulk_operations][subdir] = contrib

;--------------------
; Themes
;--------------------

projects[omega][version] = 3.1
projects[omega][subdir] = contrib

;--------------------
; Open Data
;--------------------

projects[recline][subdir] = contrib
projects[feeds][subdir] = contrib
projects[feeds][version] = 2.x
projects[data][subdir] = contrib
projects[data][version] = 1.x
projects[job_scheduler][subdir] = contrib
projects[job_scheduler][version] = 1.x
projects[entity_rdf][subdir] = contrib
projects[geofield][subdir] = contrib
projects[geophp][subdir] = contrib
projects[rdfx][subdir] = contrib

projects[field_group_table][subdir] = contrib
projects[field_group_table][download][type] = git
projects[field_group_table][download][url] = "git@github.com:nuams/field_group_table.git"
projects[field_group_table][download][revision] = "770679ad1fcb6f1da0ea32d49052aee9c5c023c0"
projects[field_group_table][type] = "module"

projects[leaflet_draw_widget][subdir] = contrib
projects[leaflet_draw_widget][download][type] = git
projects[leaflet_draw_widget][download][url] = "git://github.com/acouch/leaflet_draw_widget.git"
projects[leaflet_draw_widget][download][revision] = "33a98b1285d03b3efbce9f1652d3f78e401c3728"
projects[leaflet_draw_widget][type] = "module"

;--------------------
; Libraries
;--------------------

libraries[recline][type] = libraries
libraries[recline][download][type] = git
libraries[recline][download][url] = "git://github.com/okfn/recline.git"
libraries[recline][download][revision] = "3610d95e7474f1c5c3752ff2f73447f7be2b5546"

libraries[font_awesome][type] = libraries
libraries[font_awesome][download][type] = git
libraries[font_awesome][download][url] = "https://github.com/FortAwesome/Font-Awesome.git"
libraries[font_awesome][directory_name] = font_awesome
libraries[font_awesome][download][revision] = "13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09"

libraries[chosen][type] = libraries
libraries[chosen][download][type] = git
libraries[chosen][download][url] = "https://github.com/harvesthq/chosen.git"
libraries[chosen][directory_name] = chosen
libraries[chosen][download][revision] = "a0ca7da1ae52235b5abb6f66d9218a20450116c1"

libraries[slugify][type] = libraries
libraries[slugify][download][type] = git
libraries[slugify][download][url] = "git://github.com/pmcelhaney/jQuery-Slugify-Plugin.git"
libraries[slugify][directory_name] = slugify
libraries[slugify][download][revision] = "79133a1bdfd3ac80d500d661a722b85c03a01da3"

libraries[Leaflet.draw][type] = libraries
libraries[Leaflet.draw][download][type] = git
libraries[Leaflet.draw][download][url] = "git@github.com:Leaflet/Leaflet.draw.git"
libraries[Leaflet.draw][download][revision] = "82f4d960a44753c3a9d98001e49e03429395b53a"

libraries[Leaflet][type] = libraries
libraries[Leaflet][download][type] = git
libraries[Leaflet][download][url] = "git@github.com:Leaflet/Leaflet.git"
libraries[Leaflet][download][revision] = "81221ae4cd9772a8974b2e3c867d4fb35abd052d"

;--------------------
; Development
;--------------------

projects[omega_tools][subdir] = contrib
