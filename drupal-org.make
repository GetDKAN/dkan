---
api: '2'
core: 7.x
includes:
- https://raw.githubusercontent.com/GetDKAN/visualization_entity/7.x-2.x/visualization_entity.make
- https://raw.githubusercontent.com/GetDKAN/open_data_schema_map/7.x-2.6/open_data_schema_map.make
- https://raw.githubusercontent.com/GetDKAN/leaflet_draw_widget/5a5f8faf664aeca02371f6692307580d9fab9116/leaflet_widget.make
- https://raw.githubusercontent.com/GetDKAN/recline/7.x-2.3/recline.make
projects:
  admin_menu:
    version: 3.0-rc6
  admin_menu_source:
    version: '1.1'
    patch:
      2441283: https://www.drupal.org/files/issues/allow_ordering_of_the-2441283-5.patch
  admin_views:
    version: '1.7'
  adminrole:
    version: '1.1'
  autocomplete_deluxe:
    version: '2.3'
    patch:
      2833824: https://www.drupal.org/files/issues/autocomplete-deluxe-2833824-4.patch
  autoload:
    download:
      type: git
      url: https://git.drupal.org/project/autoload.git
      branch: 7.x-2.x
      revision: 80ea4d125a2edf1e3c68c5627b3afb4614828a27
  beautytips:
    download:
      type: git
      url: https://git.drupal.org/project/beautytips.git
      branch: 7.x-2.x
      revision: 5e8a425d
    patch:
      849232: https://www.drupal.org/files/issues/2019-08-29/include-excanvas-via-libraries-api-d7-849232-15_0.patch
      3063738: https://www.drupal.org/files/issues/2019-06-24/php72count-3063738-1.patch
  better_exposed_filters:
    version: '3.6'
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
    version: '1.15'
  data:
    version: 1.x
  date:
    download:
      type: git
      url: https://git.drupal.org/project/date.git
      branch: 7.x-2.x
      revision: a2ef952517f789bfd85659f96a0321a66936661a
  defaultconfig:
    version: 1.0-alpha11
  devel:
    version: '1.7'
  diff:
    version: '3.4'
  dkan_default_content:
    download:
        type: git
        url: https://github.com/GetDKAN/dkan_default_content.git
        tag: 7.x-1.0
  double_field:
    version: '2.5'
  drafty:
    version: 1.0-rc1
  draggableviews:
    version: '2.1'
  entity:
    download:
      full_version: 7.x-1.9
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
  environment:
    version: '1.0'
  environment_indicator:
    version: '2.9'
  facetapi:
    version: '1.6'
    patch:
      3084250: https://www.drupal.org/files/issues/2019-10-25/hide-block-title-empty-facet-3084250-5.patch
      2610702: https://www.drupal.org/files/issues/2019-11-01/facetapi-sanitize-facet-2610702-6-D7-1.x.patch
  facetapi_bonus:
    version: '1.3'
  facetapi_pretty_paths:
    version: '1.4'
  features:
    version: '2.11'
  features_roles_permissions:
    version: '1.2'
  field_group:
    version: '1.6'
  field_group_table:
    version: '1.6'
    patch:
      1: patches/field_group_table_accessibilty.patch
      2887897: https://www.drupal.org/files/issues/added_missing_isset_calls-2887897-2.patch
      3016830: https://www.drupal.org/files/issues/2018-11-28/undefined-index-classes-3016830-0.patch
  field_hidden:
    version: '1.8'
  field_reference_delete:
    download:
      full_version: 7.x-1.0-beta1
  fieldable_panels_panes:
    version: '1.13'
  file_entity:
    version: 2.27
  file_resup:
    version: '1.5'
  filefield_sources:
    version: '1.11'
  filehash:
    download:
      type: git
      url: https://git.drupal.org/project/filehash.git
      branch: '7.x-1.x'
      revision: d36daa759271737f20198240b4aa50280c95af5a
    patch:
      3088648: https://www.drupal.org/files/issues/2019-10-17/add-sha512-3088648-5.patch
      2: patches/filehash-uploaded-files-only-option.patch
  font_icon_select:
    download:
      type: git
      url: https://github.com/GetDKAN/font_icon_select.git
      branch: 7.x-1.x
  fontyourface:
    version: '2.8'
    patch:
      1: patches/fontyourface-no-ajax-browse-view.patch
      2: patches/fontyourface-clear-css-cache.patch
      2644694: https://www.drupal.org/files/issues/browse-fonts-page-uses-disabled-font-2644694.patch
      2816837: https://www.drupal.org/files/issues/font_your_face-remove_div_general_text_option-D7.patch
  globalredirect:
    version: '1.6'
    patch:
      3053515: https://www.drupal.org/files/issues/2019-05-08/globalredirect-3053515-is-dir-external-check.patch
  gravatar:
    download:
      type: git
      url: https://git.drupal.org/project/gravatar.git
      branch: 7.x-1.x
      revision: bb2f81e
    patch:
      1568162: https://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch
  honeypot:
    version: '1.26'
  image_url_formatter:
    version: '1.4'
  imagecache_actions:
    version: '1.11'
  job_scheduler:
    version: 2.0
  jquery_update:
    version: '3.0-alpha5'
  leaflet_draw_widget:
    download:
      type: git
      url: https://github.com/GetDKAN/leaflet_draw_widget.git
      revision: 5a5f8faf664aeca02371f6692307580d9fab9116
  libraries:
    version: '2.5'
  link:
    version: '1.7'
  link_badges:
    version: '1.1'
  link_iframe_formatter:
    version: '1.1'
  linkchecker:
    download:
      type: git
      url: 'https://git.drupal.org/project/linkchecker.git'
      revision: 623819d04464b26af8e216113a88cd03f4bb4ccc
    patch:
      965720: https://www.drupal.org/files/issues/linkchecker-views-integration-965720-124.patch
      1: patches/dkan_linkchecker_file.patch
  manualcrop:
    version: '1.7'
  markdown:
    version: '1.5'
  markdowneditor:
    version: '1.4'
    patch:
      2045225: https://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch
  media:
    version: 2.24
  media_youtube:
    version: '3.9'
  media_vimeo:
    version: '2.1'
    patch:
      2446199: https://www.drupal.org/files/issues/no_exception_handling-2446199-1.patch
  menu_admin_per_menu:
    version: '1.1'
  menu_badges:
    version: '1.3'
  menu_block:
    version: '2.8'
  migrate:
    version: '2.11'
    patch:
      1989492: https://www.drupal.org/files/issues/migrate-append-map-messages-1989492-10.patch
      3027630: https://www.drupal.org/files/issues/2019-01-22/3027630-migrate-php72-count-2.patch
  migrate_extras:
    version: '2.5'
  module_filter:
    version: '2.2'
  multistep:
    download:
      type: git
      url: https://git.drupal.org/project/multistep.git
      revision: 3b0d40a
  nuboot_radix:
    download:
      type: git
      url: https://github.com/GetDKAN/nuboot_radix.git
      tag: 7.x-2.0
    type: theme
  og:
    version: '2.10'
    patch:
      1090438: https://drupal.org/files/issues/og-add_users_and_entities_with_drush-1090438-12.patch
      2549071: https://www.drupal.org/files/issues/og_actions-bug-vbo-delete.patch
      2301831: https://www.drupal.org/files/issues/og-missing-permission-roles-2301831-1.patch
      2900273: https://www.drupal.org/files/issues/2019-09-11/check-if-group-is-object-2900273-16.patch
  og_extras:
    version: '1.2'
  og_moderation:
    version: '2.3'
    patch:
      2447769: https://www.drupal.org/files/issues/revision_access-2447769.patch
  open_data_schema_map:
    download:
      type: git
      url: https://github.com/GetDKAN/open_data_schema_map.git
      tag: 7.x-2.6
  panelizer:
    version: '3.4'
    patch:
      2845433: https://www.drupal.org/files/issues/2020-03-27/panelizer-fix-access-denied-2845433-42-D7.patch
  panels:
    version: '3.9'
  panels_style_collapsible:
    version: '1.3'
  panopoly_widgets:
    version: '1.70'
    patch:
      1: patches/panopoly_widgets_overrides.patch
      2: patches/panopoly_widgets_add_jquery_ui_tabs.patch
  panopoly_images:
    version: '1.72'
  path_breadcrumbs:
    version: '3.4'
  pathauto:
    version: '1.3'
  r4032login:
    version: '1.8'
  radix:
    type: theme
    version: '3.8'
    patch:
      1: patches/radix-bootstrap.patch
  radix_layouts:
    version: '3.4'
  recline:
    download:
      type: git
      url: https://github.com/GetDKAN/recline.git
      tag: 7.x-2.3
  ref_field:
    download:
      type: git
      url: https://git.drupal.org/project/ref_field.git
      revision: 9dbf7cf
    patch:
      2360019: https://www.drupal.org/files/issues/ref_field-delete-insert-warning-2360019-5.patch
  remote_stream_wrapper:
    download:
      type: git
      url: https://github.com/GetDKAN/remote_stream_wrapper.git
      revision: 20311eee8f0ba87cbb7e48788b176c34e0313a78
  role_export:
    version: '1.0'
  rules:
    version: '2.12'
  restws:
    version: '2.8'
  roleassign:
    version: '1.2'
  safeword:
    version: '1.13'
  schema:
    version: '1.2'
    revision: 08b02458694d186f8ab3bd0b24fbc738f9271108
  search_api:
    version: '1.26'
  search_api_db:
    version: '1.7'
  select_or_other:
    version: '2.24'
  services:
    version: '3.25'
  simple_gmap:
    version: '1.4'
  strongarm:
    version: '2.0'
  tablefield:
    version: '3.5'
  taxonomy_menu:
    version: '1.6'
  taxonomy_fixtures:
    download:
      type: git
      url: https://github.com/GetDKAN/taxonomy_fixtures.git
      revision: efabb2362509f80c40084109456c7483b5452b0a
  token:
    version: '1.7'
  token_tweaks:
    version: 1.x-dev
  uuid:
    version: '1.3'
  views:
    version: '3.23'
    patch:
      2543562: https://www.drupal.org/files/issues/views-use_query_group_operator_for_main_group-2543562-2.patch
      3054091: https://www.drupal.org/files/issues/2019-06-01/n3054091-14-hard.patch
  views_autocomplete_filters:
    version: '1.2'
    patch:
      2374709: https://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
      2317351: https://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch
  views_bulk_operations:
    version: '3.5'
  views_responsive_grid:
    version: '1.3'
  visualization_entity:
    download:
      type: git
      url: https://github.com/GetDKAN/visualization_entity.git
      tag: 7.x-2.0
    type: module
  workbench:
    version: '1.2'
  workbench_email:
    version: '3.12'
  workbench_moderation:
    version: '3.0'
    patch:
      2360973: https://www.drupal.org/files/issues/workbench_moderation-install-warnings-2360973-3.patch
      1512442: https://www.drupal.org/files/issues/1512442-20-workbench_moderation-fix_access_check.patch
      2252871: https://www.drupal.org/files/issues/2252871-workbench_moderation-db_update-6.patch
  xautoload:
    version: '5.7'
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
      url: https://github.com/GetDKAN/spectrum.git
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
