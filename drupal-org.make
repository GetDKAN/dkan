---
api: '2'
core: 7.x
includes:
  - "https://raw.githubusercontent.com/NuCivic/visualization_entity/7.x-1.2/visualization_entity.make"
  - "https://raw.githubusercontent.com/NuCivic/open_data_schema_map/1.13.6/open_data_schema_map.make"
  - "https://raw.githubusercontent.com/NuCivic/leaflet_draw_widget/master/leaflet_widget.make"
  - "https://raw.githubusercontent.com/NuCivic/recline/1.13.6/recline.make"
projects:
  admin_menu:
    version: '3.0-rc5'
  admin_menu_source:
    version: '1.1'
    patch:
      2441283: https://www.drupal.org/files/issues/allow_ordering_of_the-2441283-5.patch
  adminrole:
    version: '1.1'
  autocomplete_deluxe:
    version: '2.2'
    patch:
      2833824: https://www.drupal.org/files/issues/autocomplete-deluxe-2833824-4.patch
  beautytips:
    download:
      type: git
      url: http://git.drupal.org/project/beautytips.git
      branch: 7.x-2.x
      revision: f9a8b5b
    patch:
      849232: http://drupal.org/files/include-excanvas-via-libraries-api-d7-849232-13.patch
  better_exposed_filters:
    version: '3.4'
  bueditor:
    version: '1.8'
  bueditor_plus:
    version: '1.4'
  chosen:
    version: '2.1'
    patch:
      2834096: https://www.drupal.org/files/issues/chosen-accesibility_problem_with_input-0.patch
  colorizer:
    version: '1.10'
    patch:
      2227651: https://www.drupal.org/files/issues/colorizer-add-rgb-vars-2227651-4b.patch
      2599298: https://www.drupal.org/files/issues/colorizer-bug_system_cron_delete_current_css-2599298-9.patch
  color_field:
    version: '1.8'
    patch:
      2696505: https://www.drupal.org/files/issues/color_field-requirements-2696505-v2.patch
  conditional_styles:
    version: '2.2'
  context:
    version: '3.7'
  ctools:
    version: '1.12'
  data:
    version: 1.x
  date:
    version: '2.10'
  defaultconfig:
    version: 1.0-alpha11
  devel:
    version: '1.5'
  diff:
    version: '3.3'
  double_field:
    version: '2.5'
  draggableviews:
    version: '2.1'
  entity:
    download:
      full_version: 7.x-1.8
    patch:
      2341611: https://www.drupal.org/files/issues/entity-multivalue-token-replacement-fix-2341611-0.patch
      2564119: https://www.drupal.org/files/issues/Use-array-in-foreach-statement-2564119-1.patch
  entity_path:
    version: 1.x-dev
    patch:
      2809655: https://www.drupal.org/files/issues/entity-path-mysql-5-7_3.diff
  entityreference:
    version: '1.5'
  entityreference_filter:
    version: '1.7'
  facetapi:
    version: '1.5'
    patch:
      1: patches/cross-site-scripting-facets-156778.patch
  facetapi_bonus:
    version: '1.2'
  facetapi_pretty_paths:
    version: '1.4'
  features:
    version: '2.10'
  features_roles_permissions:
    version: '1.2'
  feeds:
    download:
      type: git
      url: http://git.drupal.org/project/feeds.git
      branch: 7.x-2.x
      revision: d312f4f66e98cc12b8462cd13874184589017f72
    patch:
      1127696: https://www.drupal.org/files/issues/1127696-137.patch
  feeds_field_fetcher:
    download:
      type: git
      url: http://git.drupal.org/project/feeds_field_fetcher.git
      branch: 7.x-1.x
      revision: 6725b86
    patch:
      2315425: http://www.drupal.org/files/issues/feeds_field_fetcher-typo-error-2315425-1.patch
      2829416: http://www.drupal.org/files/issues/feeds_field_fetcher_error-validation-config.patch
  feeds_flatstore_processor:
    download:
      type: git
      url: 'https://github.com/NuCivic/feeds_flatstore_processor.git'
      branch: master
  field_group:
    version: '1.5'
    patch:
      2042681: http://drupal.org/files/issues/field-group-show-ajax-2042681-8.patch
      2831815: https://www.drupal.org/files/issues/hash-location-sanitization.diff
  field_group_table:
    version: '1.6'
    patch:
      2887897: https://www.drupal.org/files/issues/added_missing_isset_calls-2887897-2.patch
  field_hidden:
    version: '1.7'
  field_reference_delete:
    download:
      full_version: 7.x-1.0-beta1
  fieldable_panels_panes:
    version: '1.11'
    patch:
      2825835: https://www.drupal.org/files/issues/2825835.patch
      2826182: https://www.drupal.org/files/issues/fieldable_panels_panes-title-shown-when-set-to-hidden-2826182-3.patch
      2826205: https://www.drupal.org/files/issues/fieldable_panels_panes-n2826205-32.patch
  file_entity:
    version: 2.3
  file_resup:
    download:
      type: git
      url: https://git.drupal.org/project/file_resup.git
      revision: 6cf030c2c139374c75527221775965ff0242df15
  filefield_sources:
    version: '1.10'
  font_icon_select:
    download:
      type: git
      url: https://git.drupal.org/sandbox/wolffereast/2319993.git
      branch: 7.x-1.x
  fontyourface:
    version: '2.8'
    patch:
      1: patches/fontyourface-no-ajax-browse-view.patch
      2: patches/fontyourface-clear-css-cache.patch
      2644694: https://www.drupal.org/files/issues/browse-fonts-page-uses-disabled-font-2644694.patch
      2816837: https://www.drupal.org/files/issues/font_your_face-remove_div_general_text_option-D7.patch
  globalredirect:
    version: '1.5'
  gravatar:
    download:
      type: git
      url: http://git.drupal.org/project/gravatar.git
      branch: 7.x-1.x
      revision: bb2f81e
    patch:
      1568162: http://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch
  honeypot:
    version: '1.22'
  image_url_formatter:
    version: '1.4'
  imagecache_actions:
    version: '1.7'
    type: module
    download:
      type: git
      url: http://git.drupal.org/project/imagecache_actions.git
      revision: cd19d2a
  job_scheduler:
    version: 2.x
  jquery_update:
    version: '2.7'
  leaflet_draw_widget:
    download:
      type: git
      url: 'https://github.com/NuCivic/leaflet_draw_widget.git'
      branch: 'master'
  libraries:
    version: '2.3'
  link:
    version: '1.4'
  link_badges:
    version: '1.1'
  link_iframe_formatter:
    version: '1.1'
  manualcrop:
    version: '1.6'
  markdown:
    version: '1.5'
  markdowneditor:
    version: '1.4'
    patch:
      2045225: http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch
  media:
    version: 2.9
    patch:
      2272567: https://www.drupal.org/files/issues/media_dialog_appears_2272567-32.patch
  media_youtube:
    version: '3.4'
  media_vimeo:
    version: '2.1'
    patch:
      2446199: https://www.drupal.org/files/issues/no_exception_handling-2446199-1.patch
  menu_admin_per_menu:
    version: '1.1'
  menu_badges:
    version: '1.3'
  menu_block:
    version: '2.7'
  migrate:
    version: '2.8'
    patch:
      1989492: https://www.drupal.org/files/issues/migrate-append-map-messages-1989492-10.patch
  migrate_extras:
    version: '2.5'
  module_filter:
    version: '2.1'
  multistep:
    download:
      type: git
      url: http://git.drupal.org/project/multistep.git
      revision: 3b0d40a
  og:
    version: '2.9'
    patch:
      1090438: http://drupal.org/files/issues/og-add_users_and_entities_with_drush-1090438-12.patch
      2549071: https://www.drupal.org/files/issues/og_actions-bug-vbo-delete.patch
      2301831: https://www.drupal.org/files/issues/og-missing-permission-roles-2301831-1.patch
  og_extras:
    version: '1.2'
  og_moderation:
    version: '2.3'
  open_data_schema_map:
    download:
      type: git
      url: https://github.com/NuCivic/open_data_schema_map.git
      branch: 1.13.6
  panelizer:
    version: '3.4'
  panels:
    version: '3.9'
  panels_style_collapsible:
    version: '1.3'
  panopoly_widgets:
    version: '1.45'
    patch:
      1: patches/panopoly_widgets_overrides.patch
      2: patches/panopoly_widgets_add_jquery_ui_tabs.patch
      3: patches/panopoly_widgets_overrides_OOB.patch
  panopoly_images:
    version: '1.45'
  path_breadcrumbs:
    version: '3.3'
  pathauto:
    version: '1.3'
  r4032login:
    version: '1.8'
  radix:
    type: theme
    version: '3.6'
  radix_layouts:
    version: '3.4'
  recline:
    download:
      type: git
      url: 'https://github.com/NuCivic/recline.git'
      branch: 1.13.6
  ref_field:
    download:
      type: git
      url: http://git.drupal.org/project/ref_field.git
      revision: 9dbf7cf
    patch:
      2360019: https://www.drupal.org/files/issues/ref_field-delete-insert-warning-2360019-5.patch
  remote_stream_wrapper:
    download:
      type: git
      url: https://github.com/NuCivic/remote_stream_wrapper.git
      branch: 7.x-1.x
  role_export:
    version: '1.0'
  rules:
    version: '2.10'
    patch:
      2406863: https://www.drupal.org/files/issues/2406863_rules-7-x-2.10-configurable_rules_stampede_protection-126.patch
      2851567: https://www.drupal.org/files/issues/rules_init_and_cache-2851567-8.patch
  restws:
    version: '2.7'
  roleassign:
    version: '1.2'
  safeword:
    version: '1.13'
  schema:
    version: '1.2'
    revision: 08b02458694d186f8ab3bd0b24fbc738f9271108
  search_api:
    version: '1.22'
  search_api_db:
    version: '1.6'
    patch:
      2855634:https://www.drupal.org/files/issues/2855634-23--fix_update_7107_for_different_db.patch
  select_or_other:
    version: '2.22'
  services:
    version: '3.20'
  simple_gmap:
    version: '1.4'
  strongarm:
    version: '2.0'
  tablefield:
    version: '2.5'
  taxonomy_menu:
    version: '1.5'
  taxonomy_fixtures:
    download:
      type: git
      url: 'https://github.com/NuCivic/taxonomy_fixtures.git'
      branch: 7.x-1.x
  token:
    version: '1.7'
  uuid:
    version: '1.0'
  views:
    version: '3.17'
  views_autocomplete_filters:
    version: '1.2'
    patch:
      2374709: http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
      2317351: http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch
  views_bulk_operations:
    version: '3.4'
  views_responsive_grid:
    version: '1.3'
  visualization_entity:
    download:
      type: git
      url: https://github.com/NuCivic/visualization_entity.git
      tag: 7.x-1.2
    type: module
  workbench:
    version: '1.2'
  workbench_email:
    version: '3.12'
  workbench_moderation:
    version: '3.0'
    patch:
      2360973: https://www.drupal.org/files/issues/workbench_moderation-install-warnings-2360973-3.patch
  drafty:
    version: 1.0-beta4
libraries:
  chosen:
    download:
      type: get
      url: https://github.com/harvesthq/chosen/releases/download/v1.3.0/chosen_v1.3.0.zip
  excanvas:
    download:
      type: git
      url: https://github.com/arv/ExplorerCanvas.git
      sha1: aa989ea9d9bac748638f7c66b0fc88e619715da6
  font_awesome:
    type: libraries
    download:
      type: git
      url: https://github.com/FortAwesome/Font-Awesome.git
      revision: 13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09
    directory_name: font_awesome
  jquery.imagesloaded:
    download:
      type: file
      url: https://github.com/desandro/imagesloaded/archive/v2.1.2.tar.gz
      subtree: imagesloaded-2.1.2
  jquery.imgareaselect:
    download:
      type: file
      url: https://github.com/odyniec/imgareaselect/archive/v0.9.11-rc.1.tar.gz
      subtree: imgareaselect-0.9.11-rc.1
  slugify:
    download:
      type: git
      url: https://github.com/pmcelhaney/jQuery-Slugify-Plugin.git
      revision: 79133a1bdfd3ac80d500d661a722b85c03a01da3
    directory_name: slugify
  spectrum:
    download:
      type: git
      url: https://github.com/NuCivic/spectrum.git
      tag: 1.8.0-civic-4736
    directory_name: bgrins-spectrum
  spyc:
    download:
      type: file
      url: https://raw.github.com/mustangostang/spyc/master/Spyc.php
    directory_name: spyc
defaults:
  projects:
    subdir: contrib
