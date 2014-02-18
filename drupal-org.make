core = 7.x
api = 2

; DKAN
projects[dkan_dataset][subdir] = dkan
projects[dkan_dataset][download][type] = git
projects[dkan_dataset][download][version] = 1.x
projects[dkan_dataset][download][url] = "https://github.com/nuams/dkan_dataset.git"
projects[dkan_dataset][type] = "module"

projects[dkan_datastore][subdir] = dkan
projects[dkan_datastore][download][type] = git
projects[dkan_datastore][download][version] = 1.x
projects[dkan_datastore][download][url] = "http://git.drupal.org/project/dkan_datastore.git"
projects[dkan_datastore][type] = "module"

; Contrib Modules
projects[autocomplete_deluxe][subdir] = contrib
projects[autocomplete_deluxe][version] = 2.0-beta3
projects[beautytips][version] = 2.x
projects[beautytips][subdir] = contrib
projects[beautytips][patch][849232] = http://drupal.org/files/include-excanvas-via-libraries-api-d7-849232-13.patch
projects[chosen][subdir] = contrib
projects[chosen][version] = 2.0-alpha1
projects[ctools][subdir] = contrib
projects[ctools][version] = 1.4
projects[diff][subdir] = contrib
projects[date][subdir] = contrib
projects[double_field][subdir] = contrib
projects[double_field][version] = 2.3
projects[entity][subdir] = contrib
projects[entity][version] = 1.3
projects[entityreference][subdir] = contrib
projects[entityreference][version] = 1.1
projects[eva][subdir] = contrib
projects[eva][version] = 1.2
projects[features][subdir] = contrib
projects[features][version] = 2.0-rc3
projects[field_group][subdir] = contrib
projects[field_group][version] = 1.3
projects[field_group][patch][2042681] = http://drupal.org/files/issues/field-group-show-ajax-2042681-8.patch
projects[filefield_sources][subdir] = contrib
projects[filefield_sources][version] = 1.9
projects[jquery_update][subdir] = contrib
projects[jquery_update][version] = 2.3
projects[libraries][subdir] = contrib
projects[libraries][version] = 2.1
projects[link][subdir] = contrib
projects[link][version] = 1.1
projects[link_iframe_formatter][subdir] = contrib
projects[multistep][subdir] = contrib
projects[multistep][version] = 1.x
projects[og][subdir] = contrib
projects[og][version] = 2.4
projects[og_extras][subdir] = contrib
projects[og_extras][version] = 1.x
projects[ref_field][subdir] = contrib
projects[ref_field][version] = 2.x
projects[ref_field][patch][1670356] = http://drupal.org/files/removed_notice-1670356-1.patch
projects[remote_file_source][subdir] = contrib
projects[remote_stream_wrapper][subdir] = contrib
projects[restws][subdir] = contrib
projects[select_or_other][subdir] = contrib
projects[select_or_other][version] = 2.20
projects[strongarm][subdir] = contrib
projects[token][subdir] = contrib
projects[uuid][subdir] = contrib
projects[uuid][version] = 1.0-alpha5
projects[uuid][patch][1927474] = http://drupal.org/files/unknown-column-in-field-list-fix-1927474-2.patch
projects[views][subdir] = contrib
projects[views_datasource][subdir] = contrib
projects[views_datasource][download][type] = git
projects[views_datasource][download][url] = "http://git.drupal.org/project/views_datasource.git"
projects[views_datasource][download][branch] = "7.x-1.x"
projects[views_datasource][type] = "module"
projects[views_bulk_operations][subdir] = contrib

projects[recline][subdir] = contrib
projects[recline][version] = 1.x
projects[entity_rdf][subdir] = contrib
projects[geofield][subdir] = contrib
projects[geofield][version] = 1.2
projects[geophp][subdir] = contrib
projects[geophp][version] = 1.7
projects[rdfx][subdir] = contrib
projects[rdfx][version] = 2.x
projects[rdfx][patch][1271498] = http://drupal.org/files/issues/1271498_3_rdfui_form_values.patch

projects[field_group_table][subdir] = contrib
projects[field_group_table][download][type] = git
projects[field_group_table][download][url] = "https://github.com/nuams/field_group_table.git"
projects[field_group_table][type] = "module"

projects[leaflet_draw_widget][subdir] = contrib
projects[leaflet_draw_widget][download][type] = git
projects[leaflet_draw_widget][download][url] = "https://github.com/acouch/leaflet_draw_widget.git"
projects[leaflet_draw_widget][download][revision] = "33a98b1285d03b3efbce9f1652d3f78e401c3728"
projects[leaflet_draw_widget][type] = "module"

projects[feeds][subdir] = "contrib"
projects[feeds][version] = "2.x"
projects[feeds][download][type] = "git"
projects[feeds][download][url] = "http://git.drupal.org/project/feeds.git"
projects[feeds][download][revision] = 1383713
projects[feeds][download][branch] = 7.x-2.x
projects[feeds][type] = "module"
projects[feeds][patch][1428272] = http://drupal.org/files/feeds-encoding_support_CSV-1428272-52.patch
projects[feeds][patch][1127696] = http://drupal.org/files/feeds-1127696-multiple-importers-per-content-type-59.patch

projects[feeds_field_fetcher][subdir] = contrib
projects[feeds_field_fetcher][download][type] = git
projects[feeds_field_fetcher][download][url] = "http://git.drupal.org/project/feeds_field_fetcher.git"
projects[feeds_field_fetcher][download][branch] = master
projects[feeds_field_fetcher][type] = "module"

projects[feeds_flatstore_processor][subdir] = contrib
projects[feeds_flatstore_processor][download][type] = git
projects[feeds_flatstore_processor][download][url] = "http://git.drupal.org/sandbox/acouch/1952754.git"
projects[feeds_flatstore_processor][download][branch] = master
projects[feeds_flatstore_processor][type] = "module"

projects[schema][subdir] = contrib
projects[schema][patch][1237974] = http://drupal.org/files/schema-support-custom-types-1237974-48.patch
projects[services][subdir] = contrib
projects[data][subdir] = contrib
projects[data][version] = 1.x
projects[job_scheduler][subdir] = contrib
projects[job_scheduler][version] = 2.x

projects[admin_menu][subdir] = contrib
projects[bueditor][subdir] = contrib
projects[bueditor][patch][1931862] = http://drupal.org/files/dont-render-bueditor-for-plain-text-textareas.patch
projects[context][subdir] = contrib
projects[delta][subdir] = contrib
projects[defaultcontent][subdir] = contrib
projects[defaultcontent][version] = 1.x
projects[defaultcontent][patch][1436094] = http://drupal.org/files/node_sort_value-1436094-6.patch
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
projects[search_api][subdir] = contrib
projects[search_api_db][subdir] = contrib
projects[rules][subdir] = contrib
projects[rules][version] = 2.3

; Themes
projects[omega][version] = 3.1
projects[omega][subdir] = contrib
projects[omega][patch][1828552] = http://drupal.org/files/1828552-omega-hook_views_mini_pager.patch

; Libraries
libraries[recline][type] = libraries
libraries[recline][download][type] = git
libraries[recline][download][url] = "https://github.com/okfn/recline.git"
libraries[recline][download][revision] = "e007fff15ac6e3853d96f095986cae6e4b192471"

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
libraries[slugify][download][url] = "https://github.com/pmcelhaney/jQuery-Slugify-Plugin.git"
libraries[slugify][directory_name] = slugify
libraries[slugify][download][revision] = "79133a1bdfd3ac80d500d661a722b85c03a01da3"

libraries[Leaflet.draw][type] = libraries
libraries[Leaflet.draw][download][type] = git
libraries[Leaflet.draw][download][url] = "https://github.com/Leaflet/Leaflet.draw.git"
libraries[Leaflet.draw][download][revision] = "82f4d960a44753c3a9d98001e49e03429395b53a"

libraries[Leaflet][type] = libraries
libraries[Leaflet][download][type] = git
libraries[Leaflet][download][url] = "https://github.com/Leaflet/Leaflet.git"
libraries[Leaflet][download][revision] = "81221ae4cd9772a8974b2e3c867d4fb35abd052d"

libraries[recline][type] = libraries
libraries[recline][download][type] = git
libraries[recline][download][url] = "https://github.com/okfn/recline.git"
libraries[recline][download][revision] = "e007fff15ac6e3853d96f095986cae6e4b192471"

libraries[arc][type] = libraries
libraries[arc][download][type] = git
libraries[arc][download][url] = "https://github.com/semsol/arc2.git"
libraries[arc][download][revision] = "44c396ab54178086c09499a1704e31a977b836d2"
libraries[arc][subdir] = "ARC2"

libraries[excanvas][download][type] = "file"
libraries[excanvas][download][url] = "https://explorercanvas.googlecode.com/files/excanvas_r3.zip"
libraries[excanvas][download][sha1] = "f1b9f7a44428eb0c7b27fe8ac0242d34ec94a385"

libraries[chosen][download][type] = git
libraries[chosen][download][url] = https://github.com/harvesthq/chosen.git

libraries[spyc][download][type] = "get"
libraries[spyc][download][url] = "https://raw.github.com/mustangostang/spyc/79f61969f63ee77e0d9460bc254a27a671b445f3/spyc.php"
libraries[spyc][filename] = "../spyc.php"
libraries[spyc][directory_name] = "lib"
libraries[spyc][destination] = "modules/contrib/services/servers/rest_server"
