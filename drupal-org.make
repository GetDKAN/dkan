core = 7.x
api = 2

; Set the default subdirectory for projects.
defaults[projects][subdir] = contrib

; Leaflet Draw Widget specific
projects[leaflet_draw_widget][download][type] = git
projects[leaflet_draw_widget][download][url] = "https://github.com/NuCivic/leaflet_draw_widget.git"
projects[leaflet_draw_widget][download][revision] = "967c8bb3eb13f3b70f28a4b487074b23591f1075"
projects[leaflet_draw_widget][type] = module

includes[leaflet_draw_widget_make] = https://raw.githubusercontent.com/NuCivic/leaflet_draw_widget/967c8bb3eb13f3b70f28a4b487074b23591f1075/leaflet_widget.make

; Recline specific
projects[recline][download][type] = git
projects[recline][download][url] = https://github.com/NuCivic/recline.git
projects[recline][download][revision] = a6af472a07d520a758f14cdf836a48c33e15bf07
projects[recline][download][branch] = 7.x-1.x

includes[recline_make] = https://raw.githubusercontent.com/NuCivic/recline/431ffeaf4e22845fc83d1b4361a4e1d756e055ef/recline.make

; DKAN
projects[dkan_dataset][subdir] = dkan
projects[dkan_dataset][download][type] = git
projects[dkan_dataset][download][url] = https://github.com/NuCivic/dkan_dataset.git
projects[dkan_dataset][download][branch] = 7.x-1.x

projects[dkan_datastore][subdir] = dkan
projects[dkan_datastore][download][type] = git
projects[dkan_datastore][download][url] = https://github.com/NuCivic/dkan_datastore.git
projects[dkan_datastore][download][branch] = 7.x-1.x

includes[dkan_datastore_make] = https://raw.githubusercontent.com/NuCivic/dkan_datastore/7.x-1.x/dkan_datastore.make

projects[file_entity][patch][2308737] = https://www.drupal.org/files/issues/file_entity-remove-field-status-check-2308737-9509141.patch

; Contrib Modules
projects[admin_menu][version] = 3.0-rc5

projects[autocomplete_deluxe][version] = 2.0-beta3

projects[beautytips][download][type] = git
projects[beautytips][download][branch] = 7.x-2.x
projects[beautytips][download][url] = "http://git.drupal.org/project/beautytips.git"
projects[beautytips][download][revision] = "472248d"
projects[beautytips][patch][849232] = http://drupal.org/files/include-excanvas-via-libraries-api-d7-849232-13.patch
projects[beautytips][type] = module

projects[bueditor][version] = 1.7
projects[bueditor][patch][1931862] = http://drupal.org/files/dont-render-bueditor-for-plain-text-textareas.patch

projects[colorizer][version] = 1.4
projects[colorizer][patch][2227651] = https://www.drupal.org/files/issues/colorizer-add-rgb-vars-2227651-4b.patch
projects[colorizer][patch][2444249] = https://www.drupal.org/files/issues/colorizer-2444249.patch

projects[chosen][version] = 2.0-alpha1

projects[context][version] = 3.6

projects[ctools][version] = 1.7

projects[date][version] = 2.8

projects[defaultconfig][version] = 1.0-alpha9

projects[diff][version] = 3.2

projects[double_field][version] = 2.3

projects[draggableviews][version] = 2.1

projects[entityreference][version] = 1.1

projects[entityreference_filter][version] = 1.5

projects[entity][download][version] = 1.6
projects[entity][patch][2341611] = https://www.drupal.org/files/issues/entity-multivalue-token-replacement-fix-2341611-0.patch

projects[entity_rdf][download][type] = git
projects[entity_rdf][download][url] = http://git.drupal.org/project/entity_rdf.git
projects[entity_rdf][download][revision] = 7d91983
projects[entity_rdf][type] = module

projects[eva][version] = 1.2

projects[facetapi][version] = 1.3

projects[facetapi_pretty_paths][version] = 1.1

projects[facetapi_bonus][version] = 1.1

projects[features][version] = 2.0

projects[field_group][version] = 1.3
projects[field_group][patch][2042681] = http://drupal.org/files/issues/field-group-show-ajax-2042681-8.patch

projects[field_group_table][download][type] = git
projects[field_group_table][download][url] = "https://github.com/nuams/field_group_table.git"
projects[field_group_table][type] = module

projects[fieldable_panels_panes][version] = 1.6

projects[filefield_sources][version] = 1.9

projects[gravatar][download][type] = git
projects[gravatar][download][url] = "http://git.drupal.org/project/gravatar.git"
projects[gravatar][download][revision] = e933db3
projects[gravatar][patch][1568162] = http://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch
projects[gravatar][type] = module

projects[honeypot][version] = 1.17

projects[fontyourface][version] = 2.8

projects[imagecache_actions][download][type] = git
projects[imagecache_actions][download][url] = "http://git.drupal.org/project/imagecache_actions.git"
projects[imagecache_actions][download][branch] = 7.x-1.x
projects[imagecache_actions][download][revision] = cd19d2a
projects[imagecache_actions][type] = module

projects[jquery_update][version] = 2.3

projects[libraries][version] = 2.1

projects[link][version] = 1.1

projects[link_iframe_formatter][download][type] = git
projects[link_iframe_formatter][download][url] = "http://git.drupal.org/project/link_iframe_formatter.git"
projects[link_iframe_formatter][download][revision] = 228f9f4
projects[link_iframe_formatter][patch][2287233] = https://www.drupal.org/files/issues/link_iframe_formatter-coding-standards.patch
projects[link_iframe_formatter][type] = module

projects[markdown][version] = 1.2

projects[markdowneditor][version] = 1.2
projects[markdowneditor][patch][2045225] = http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch

projects[module_filter][version] = 2.0


projects[multistep][download][type] = git
projects[multistep][download][url] = "http://git.drupal.org/project/multistep.git"
projects[multistep][download][revision] = 3b0d40a
projects[multistep][type] = module

projects[og][version] = 2.7
projects[og][patch][1090438] = http://drupal.org/files/issues/og-add_users_and_entities_with_drush-1090438-12.patch

projects[og_extras][download][type] = git
projects[og_extras][download][url] = "http://git.drupal.org/project/og_extras.git"
projects[og_extras][download][revision] = "b7e3587"
projects[og_extras][type] = module

projects[og_moderation][version] = 2.2
projects[og_moderation][patch][2231737] = https://drupal.org/files/issues/any-user-with-view-revision-can-revert-delete-2231737-1.patch

projects[open_data_schema_map][type] = module
projects[open_data_schema_map][download][type] = git
projects[open_data_schema_map][download][url] = https://github.com/NuCivic/open_data_schema_map.git
projects[open_data_schema_map][download][branch] = master
projects[open_data_schema_map][download][revision] = ae28f32ae48c986f2dad64b7fa8bfb35b90947ac

projects[open_data_schema_map_dkan][type] = module
projects[open_data_schema_map_dkan][download][type] = git
projects[open_data_schema_map_dkan][download][url] = https://github.com/NuCivic/open_data_schema_map_dkan.git
projects[open_data_schema_map_dkan][download][branch] = master
projects[open_data_schema_map_dkan][download][revision] = 6e4f1558b4d0a3bd8da9ce8d516041d71bfa9f06

projects[panopoly_widgets][version] = 1.21
includes[panopoly_widgets_make] = http://cgit.drupalcode.org/panopoly_widgets/plain/panopoly_widgets.make

projects[panopoly_images][version] = 1.21
includes[panopoly_images_make] = http://cgit.drupalcode.org/panopoly_images/plain/panopoly_images.make

projects[panels][version] = 3.5

projects[path_breadcrumbs][version] = 3.2

projects[pathauto][version] = 1.2
projects[pathauto][version] = 1.2

projects[r4032login][version] = 1.7

projects[radix_layouts][version] = 3.3

projects[rdfx][download][type] = git
projects[rdfx][download][url] = http://git.drupal.org/project/rdfx.git
projects[rdfx][download][branch] = 7.x-2.x
projects[rdfx][download][revision] = cc7d4fc
projects[rdfx][patch][1271498] = http://drupal.org/files/issues/1271498_3_rdfui_form_values.patch

projects[ref_field][download][type] = git
projects[ref_field][download][url] = "http://git.drupal.org/project/ref_field.git"
projects[ref_field][patch][1670356] = http://drupal.org/files/removed_notice-1670356-1.patch
projects[ref_field][patch][2360019] = https://www.drupal.org/files/issues/ref_field-delete-insert-warning-2360019-0.patch
projects[ref_field][download][revision] = 9dbf7cf
projects[ref_field][type] = module

projects[remote_file_source][version] = 1.x
projects[remote_file_source][patch][2362487] = https://www.drupal.org/files/issues/remote_file_source-location-content-dist.patch

projects[remote_stream_wrapper][version] = 1.0-beta4

projects[rules][version] = 2.3

projects[restws][version] = 2.3

projects[schema][version] = 1.2

projects[select_or_other][version] = 2.20

projects[search_api][version] = 1.11

projects[search_api_db][version] = 1.2

projects[strongarm][version] = 2.0

projects[token][version] = 1.5

projects[uuid][version] = 1.0-alpha5
projects[uuid][patch][1927474] = http://drupal.org/files/unknown-column-in-field-list-fix-1927474-2.patch

projects[views][version] = 3.11

projects[views_autocomplete_filters][version] = 1.1
projects[views_autocomplete_filters][patch][2277453] = http://drupal.org/files/issues/ViewsAutocompleteFilters-no_results_on_some_environments-2277453-1.patch
projects[views_autocomplete_filters][patch][2374709] = http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
projects[views_autocomplete_filters][patch][2317351] = http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch

projects[views_bulk_operations][version] = 3.2

projects[views_responsive_grid][version] = 1.3

; Deprecated
projects[delta][version] = 3.0-beta11

; Themes
projects[omega][version] = 3.1
projects[omega][patch][1828552] = http://drupal.org/files/1828552-omega-hook_views_mini_pager.patch

projects[bootstrap][download][version] = 3.x
projects[bootstrap][download][type] = git
projects[bootstrap][download][revision] = "0390173732439fd60e898c7086219ab8c99c2f3d"

projects[nuboot][subdir] = contrib
projects[nuboot][download][type] = git
projects[nuboot][download][url] = https://github.com/NuCivic/nuboot.git
projects[nuboot][download][revision] = "fbd7ea2c2f1fa45a5f5a10b4215950940335879e"
projects[nuboot][download][branch] = 7.x-1.x

projects[nuboot_radix][download][type] = git
projects[nuboot_radix][download][url] = https://github.com/NuCivic/nuboot_radix.git
projects[nuboot_radix][download][branch] = 7.x-1.x
projects[nuboot_radix][download][revision] = "08d5af72e590a56a2e6ec543f82957171acae245"
projects[nuboot_radix][type] = theme

projects[radix][type] = theme

; Libraries

libraries[arc][type] = libraries
libraries[arc][download][type] = git
libraries[arc][download][url] = "https://github.com/semsol/arc2.git"
libraries[arc][download][revision] = "44c396ab54178086c09499a1704e31a977b836d2"
libraries[arc][subdir] = "ARC2"

libraries[chosen][type] = libraries
libraries[chosen][download][type] = git
libraries[chosen][download][url] = "https://github.com/harvesthq/chosen.git"
libraries[chosen][directory_name] = chosen
libraries[chosen][download][revision] = "a0ca7da1ae52235b5abb6f66d9218a20450116c1"

libraries[excanvas][download][type] = "file"
libraries[excanvas][download][url] = "https://explorercanvas.googlecode.com/files/excanvas_r3.zip"
libraries[excanvas][download][sha1] = "f1b9f7a44428eb0c7b27fe8ac0242d34ec94a385"

libraries[font_awesome][type] = libraries
libraries[font_awesome][download][type] = git
libraries[font_awesome][download][url] = "https://github.com/FortAwesome/Font-Awesome.git"
libraries[font_awesome][directory_name] = font_awesome
libraries[font_awesome][download][revision] = "13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09"

libraries[jquery.imagesloaded][download][type] = file
libraries[jquery.imagesloaded][download][url] = https://github.com/desandro/imagesloaded/archive/v2.1.2.tar.gz
libraries[jquery.imagesloaded][download][subtree] = imagesloaded-2.1.2

libraries[jquery.imgareaselect][download][type] = file
libraries[jquery.imgareaselect][download][url] = http://odyniec.net/projects/imgareaselect/jquery.imgareaselect-0.9.10.zip
libraries[jquery.imgareaselect][download][subtree] = jquery.imgareaselect-0.9.10

libraries[slugify][type] = libraries
libraries[slugify][download][type] = git
libraries[slugify][download][url] = "https://github.com/pmcelhaney/jQuery-Slugify-Plugin.git"
libraries[slugify][directory_name] = slugify
libraries[slugify][download][revision] = "79133a1bdfd3ac80d500d661a722b85c03a01da3"

libraries[spyc][download][type] = "get"
libraries[spyc][download][url] = "https://raw.github.com/mustangostang/spyc/79f61969f63ee77e0d9460bc254a27a671b445f3/spyc.php"
libraries[spyc][filename] = "../spyc.php"
libraries[spyc][directory_name] = "lib"
libraries[spyc][destination] = "modules/contrib/services/servers/rest_server"
