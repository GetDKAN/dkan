core = 7.x
api = 2

; Contrib Modules
projects[autocomplete_deluxe][subdir] = contrib
projects[autocomplete_deluxe][version] = 2.0-beta3

projects[beautytips][download][type] = git
projects[beautytips][download][branch] = 7.x-2.x
projects[beautytips][download][url] = "http://git.drupal.org/project/beautytips.git"
projects[beautytips][download][revision] = "472248d"
projects[beautytips][patch][849232] = http://drupal.org/files/include-excanvas-via-libraries-api-d7-849232-13.patch
projects[beautytips][subdir] = contrib
projects[beautytips][type] = module

projects[chosen][version] = 2.0-alpha1
projects[chosen][subdir] = contrib

projects[context][subdir] = contrib

projects[ctools][version] = 1.4
projects[ctools][subdir] = contrib

projects[date][version] = 2.7
projects[date][subdir] = contrib

projects[double_field][version] = 2.3
projects[double_field][subdir] = contrib

projects[entity][version] = 1.4
projects[entity][subdir] = contrib

projects[entityreference][version] = 1.1
projects[entityreference][subdir] = contrib

projects[eva][version] = 1.2
projects[eva][subdir] = contrib

projects[facetapi][subdir] = contrib
projects[facetapi][version] = 1.3

projects[facetapi_pretty_paths][subdir] = contrib
projects[facetapi_pretty_paths][revision] = 1.0

projects[features][version] = 2.0
projects[features][subdir] = contrib

projects[field_group][version] = 1.3
projects[field_group][patch][2042681] = http://drupal.org/files/issues/field-group-show-ajax-2042681-8.patch
projects[field_group][subdir] = contrib

projects[filefield_sources][version] = 1.9
projects[filefield_sources][subdir] = contrib

projects[gravatar][downloald][type] =  git
projects[gravatar][download][url] = "http://git.drupal.org/project/gravatar.git"
projects[gravatar][download][branch] = 7.x-1.x
projects[gravatar][download][revision] = e933db3
projects[gravatar][patch][1568162] = http://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch
projects[gravatar][patch][1689850] = http://drupal.org/files/issues/gravatar-image_url_munged_when_pic_display_style_selected-1689850-19.patch
projects[gravatar][subdir] = contrib
projects[gravatar][type] = module

projects[jquery_update][version] = 2.3
projects[jquery_update][subdir] = contrib

projects[libraries][version] = 2.1
projects[libraries][subdir] = contrib

projects[link][version] = 1.1
projects[link][subdir] = contrib

projects[link_iframe_formatter][version] = 1.1
projects[link_iframe_formatter][subdir] = contrib

projects[multistep][download][type] = git
projects[multistep][download][url] = "http://git.drupal.org/project/multistep.git"
projects[multistep][download][revision] = 3b0d40a
projects[multistep][subdir] = contrib
projects[multistep][type] = module

projects[ref_field][download][type] = git
projects[ref_field][download][url] = "http://git.drupal.org/project/ref_field.git"
projects[ref_field][download][patch][1670356] = http://drupal.org/files/removed_notice-1670356-1.patch
projects[ref_field][download][patch][2201735] = https://drupal.org/files/issues/ref_field-invalid_argument_supplied_for_foreach-2201735-2.patch
projects[ref_field][download][revision] = 9dbf7cf
projects[ref_field][subdir] = contrib
projects[ref_field][type] = module

projects[remote_file_source][version] = 1.0
projects[remote_file_source][subdir] = contrib

projects[remote_stream_wrapper][version] = 1.0-beta4
projects[remote_stream_wrapper][subdir] = contrib

projects[select_or_other][version] = 2.20
projects[select_or_other][subdir] = contrib

projects[search_api][version] = 1.11
projects[search_api][subdir] = contrib

projects[search_api_db][version] = 1.2
projects[search_api_db][subdir] = contrib

projects[strongarm][version] = 2.0
projects[strongarm][subdir] = contrib

projects[token][version] = 1.5
projects[token][subdir] = contrib

projects[uuid][version] = 1.0-alpha5
projects[uuid][patch][1927474] = http://drupal.org/files/unknown-column-in-field-list-fix-1927474-2.patch
projects[uuid][subdir] = contrib

projects[views][version] = 3.7
projects[views][subdir] = contrib

projects[recline][version] = 1.0
projects[recline][subdir] = contrib

projects[entity_rdf][download][type] = git
projects[entity_rdf][download][url] = http://git.drupal.org/project/entity_rdf.git
projects[entity_rdf][download][revision] = 7d91983
projects[entity_rdf][type] = module
projects[entity_rdf][subdir] = contrib

projects[geofield][version] = 1.2
projects[geofield][subdir] = contrib

projects[geophp][version] = 1.7
projects[geophp][subdir] = contrib

projects[rdfx][download][type] = git
projects[rdfx][download][url] = http://git.drupal.org/project/rdfx.git
projects[rdfx][download][branch] = 7.x-2.x
projects[rdfx][download][revision] = cc7d4fc
projects[rdfx][patch][1271498] = http://drupal.org/files/issues/1271498_3_rdfui_form_values.patch
projects[rdfx][subdir] = contrib

projects[field_group_table][download][type] = git
projects[field_group_table][download][url] = "https://github.com/nuams/field_group_table.git"
projects[field_group_table][subdir] = contrib
projects[field_group_table][type] = module

projects[leaflet_draw_widget][download][type] = git
projects[leaflet_draw_widget][download][url] = "https://github.com/acouch/leaflet_draw_widget.git"
projects[leaflet_draw_widget][download][revision] = "33a98b1285d03b3efbce9f1652d3f78e401c3728"
projects[leaflet_draw_widget][subdir] = contrib
projects[leaflet_draw_widget][type] = module


projects[imagecache_actions][download][type] = git
projects[imagecache_actions][download][url] = "http://git.drupal.org/project/imagecache_actions.git"
projects[imagecache_actions][download][branch] = 7.x-1.x
projects[imagecache_actions][download][revision] = cd19d2a
projects[imagecache_actions][subdir] = contrib
projects[imagecache_actions][type] = module

; Libraries
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
libraries[recline][download][revision] = "300e5ea6a74af4b332b10ff8710d5173d2201dfc"

libraries[arc][type] = libraries
libraries[arc][download][type] = git
libraries[arc][download][url] = "https://github.com/semsol/arc2.git"
libraries[arc][download][revision] = "44c396ab54178086c09499a1704e31a977b836d2"
libraries[arc][subdir] = "ARC2"

libraries[excanvas][download][type] = "file"
libraries[excanvas][download][url] = "https://explorercanvas.googlecode.com/files/excanvas_r3.zip"
libraries[excanvas][download][sha1] = "f1b9f7a44428eb0c7b27fe8ac0242d34ec94a385"
