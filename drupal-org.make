api: '2'
core: 7.x
includes:
  - "https://raw.githubusercontent.com/NuCivic/visualization_entity/7.x-1.0-beta2/visualization_entity.make"
  - "https://raw.githubusercontent.com/NuCivic/open_data_schema_map/7.x-1.x/open_data_schema_map.make"
  - "https://raw.githubusercontent.com/NuCivic/leaflet_draw_widget/master/leaflet_widget.make"
  - "https://raw.githubusercontent.com/NuCivic/recline/7.x-1.x/recline.make"
projects:
  admin_menu:
    version: '3.0-rc5'
  admin_menu_source:
    version: '1.1'
    # Allow ordering of roles to handle users w/multiple roles
    patch:
      2441283: 'https://www.drupal.org/files/issues/allow_ordering_of_the-2441283-5.patch'
  adminrole:
    version: '1.1'
  autocomplete_deluxe:
    version: '2.1'
  beautytips:
    download:
      type: git
      url: 'http://git.drupal.org/project/beautytips.git'
      branch: 7.x-2.x
      revision: f9a8b5b
    patch:
      849232: 'http://drupal.org/files/include-excanvas-via-libraries-api-d7-849232-13.patch'
  better_exposed_filters:
    version: '3.2'
  bueditor:
    version: '1.8'
  bueditor_plus:
    version: '1.4'
  chosen:
    version: '2.0-beta5'
  colorizer:
    version: '1.10'
    patch:
      2227651: 'https://www.drupal.org/files/issues/colorizer-add-rgb-vars-2227651-4b.patch'
      2599298: 'https://www.drupal.org/files/issues/colorizer-bug_system_cron_delete_current_css-2599298-9.patch'
  color_field:
    version: '1.8'
    patch:
      2696505: 'https://www.drupal.org/files/issues/color_field-requirements-2696505-v2.patch'
  conditional_styles:
    version: '2.2'
  context:
    version: '3.6'
  ctools:
    version: '1.11'
  data:
    version: '1.x'
  date:
    version: '2.9'
  defaultconfig:
    version: '1.0-alpha11'
  diff:
    version: '3.2'
  double_field:
    version: '2.4'
  draggableviews:
    version: '2.1'
  entity:
    download:
      full_version: '7.x-1.7'
    patch:
      2341611: 'https://www.drupal.org/files/issues/entity-multivalue-token-replacement-fix-2341611-0.patch'
      2564119: 'https://www.drupal.org/files/issues/Use-array-in-foreach-statement-2564119-1.patch'
  entity_path:
    version: '1.x-dev'
    patch:
      2809655: 'https://www.drupal.org/files/issues/entity-path-mysql-5-7_3.diff'
  entityreference:
    version: '1.1'
  entityreference_filter:
    version: '1.5'
  eva:
    version: '1.2'
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
      url: 'http://git.drupal.org/project/feeds.git'
      branch: 7.x-2.x
      revision: 453dddfa5d8b2bc8c5961466490aa385f57655b2
    patch:
      1428272: 'http://drupal.org/files/feeds-encoding_support_CSV-1428272-52.patch'
      1127696: 'http://drupal.org/files/issues/1127696-97.patch'
  feeds_field_fetcher:
    download:
      type: git
      url: 'http://git.drupal.org/project/feeds_field_fetcher.git'
      branch: 7.x-1.x
      revision: 6725b86
  feeds_flatstore_processor:
    download:
      type: git
      url: 'https://github.com/NuCivic/feeds_flatstore_processor.git'
      branch: master
  field_group:
    version: '1.5'
    patch:
      2042681: 'http://drupal.org/files/issues/field-group-show-ajax-2042681-8.patch'
  field_group_table:
    download:
      type: git
      url: 'https://github.com/nuams/field_group_table.git'
      revision: 5b0aed9396a8cfd19a5b623a5952b3b8cacd361c
  field_hidden:
    version: '1.7'
  field_reference_delete:
    download:
      full_version: 7.x-1.0-beta1
  fieldable_panels_panes:
    version: '1.10'
  file_entity:
    version: '2.0-beta2'
    patch:
      2308737: 'https://www.drupal.org/files/issues/file_entity-remove-field-status-check-2308737-9509141.patch'
  file_resup:
    version: '1.x-dev'
  filefield_sources:
    version: '1.10'
  font_icon_select:
    download:
      type: git
      url: 'https://git.drupal.org/sandbox/wolffereast/2319993.git'
      branch: 7.x-1.x
  fontyourface:
    version: '2.8'
    patch:
      1: patches/fontyourface-no-ajax-browse-view.patch
      2: patches/fontyourface-clear-css-cache.patch
      2644694: 'https://www.drupal.org/files/issues/browse-fonts-page-uses-disabled-font-2644694.patch'
      2816837: 'https://www.drupal.org/files/issues/font_your_face-remove_div_general_text_option-D7.patch'
  globalredirect:
    version: '1.5'
  gravatar:
    download:
      type: git
      url: 'http://git.drupal.org/project/gravatar.git'
      branch: 7.x-1.x
      revision: bb2f81e
    patch:
      1568162: 'http://drupal.org/files/views-display-user-picture-doesn-t-display-gravatar-1568162-10.patch'
  honeypot:
    version: '1.22'
  image_url_formatter:
    version: '1.4'
  imagecache_actions:
    version: '1.7'
    type: module
    download:
      type: git
      url: 'http://git.drupal.org/project/imagecache_actions.git'
      revision: cd19d2a
  job_scheduler:
    version: '2.x'
  jquery_update:
    version: '2.7'
  leaflet_draw_widget:
    download:
      type: git
      url: 'https://github.com/NuCivic/leaflet_draw_widget.git'
      branch: master
  libraries:
    version: '2.2'
  link:
    version: '1.4'
  link_badges:
    version: '1.1'
  link_iframe_formatter:
    download:
      type: git
      url: 'http://git.drupal.org/project/link_iframe_formatter.git'
      revision: 228f9f4
    patch: 
      2287233: 'https://www.drupal.org/files/issues/link_iframe_formatter-coding-standards.patch'
  manualcrop:
    version: '1.5'
    patch:
      2504119: 'https://www.drupal.org/files/issues/multiple_versions-2504119-26.patch'
  markdown:
    version: '1.4'
  markdowneditor:
    version: '1.4'
    patch:
      2045225: 'http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch'
  media:
    version: '2.0-beta1'
    patch:
      2126697: 'https://www.drupal.org/files/issues/media_wysiwyg_2126697-53.patch'
      2308487: 'https://www.drupal.org/files/issues/media-alt-title-double-encoded-2308487-2.patch'
      2084287: 'http://www.drupal.org/files/issues/media-file-name-focus-2084287-2.patch'
      2534724: 'https://www.drupal.org/files/issues/media-browser_opens_twice-2534724-53.patch'
  media_youtube:
    version: '3.0'
  media_vimeo:
    version: '2.1'
    patch:
      2446199: 'https://www.drupal.org/files/issues/no_exception_handling-2446199-1.patch' 
  menu_admin_per_menu:
    version: '1.1'
  menu_badges:
    version: '1.2'
  menu_block:
    version: '2.7'
  menu_token:
    version: '1.0-beta5'
  migrate:
    version: '2.8'
    patch:
      1989492: 'https://www.drupal.org/files/issues/migrate-append-map-messages-1989492-10.patch'
  migrate_extras:
    version: '2.5'
  module_filter:
    version: '2.0'
  multistep:
    download:
      type: git
      url: 'http://git.drupal.org/project/multistep.git'
      revision: 3b0d40a
  og:
    version: '2.9'
    patch:
      1090438: 'http://drupal.org/files/issues/og-add_users_and_entities_with_drush-1090438-12.patch'
      2549071: 'https://www.drupal.org/files/issues/og_actions-bug-vbo-delete.patch'
      2301831: 'https://www.drupal.org/files/issues/og-missing-permission-roles-2301831-1.patch'
  og_extras:
    version: '1.2'
  og_moderation:
    version: '2.3'
  open_data_schema_map:
    download:
      type: git
      url: 'https://github.com/NuCivic/open_data_schema_map.git'
      branch: 7.x-1.x
  panelizer:
    version: '3.4'
  panels:
    version: '3.6'
    patch:
      2785915: https://www.drupal.org/files/issues/panels-storage-backcompat-2785915-18.patch
  panels_style_collapsible:
    version: '1.3'
  panopoly_widgets:
    version: '1.37'
    patch:
      1: patches/panopoly_widgets_overrides.patch
      2: patches/panopoly_widgets_add_jquery_ui_tabs.patch
  panopoly_images:
    version: '1.37'
  path_breadcrumbs:
    version: '3.3'
  pathauto:
    version: '1.3'
  r4032login:
    version: '1.8'
  radix:
    type: theme
    version: '3.3'
  radix_layouts:
    version: '3.4'
  recline:
    download:
      type: git
      url: 'https://github.com/NuCivic/recline.git'
      branch: 7.x-1.x
  ref_field:
    download:
      type: git
      url: 'http://git.drupal.org/project/ref_field.git'
      revision: 9dbf7cf
    patch:
      2360019: 'https://www.drupal.org/files/issues/ref_field-delete-insert-warning-2360019-5.patch'
  remote_file_source:
    version: '1.x'
    patch:
      2362487: 'https://www.drupal.org/files/issues/remote_file_source-location-content-dist_1.patch'
  remote_stream_wrapper:
    version: '1.0-rc1'
  role_export:
    version: '1.0'
  rules:
    version: '2.9'
  restws:
    version: '2.6'
  roleassign:
    version: '1.1'
  safeword:
    version: '1.13'
  schema:
    version: '1.2'
    revision: 08b02458694d186f8ab3bd0b24fbc738f9271108
  search_api:
    version: '1.18'
  search_api_db:
    version: '1.5'
  select_or_other:
    version: '2.22'
  services:
    version: '3.16'
  simple_gmap:
    version: '1.2'
  strongarm:
    version: '2.0'  
  tablefield:
    version: '2.4'
  taxonomy_menu:
    version: '1.5'
  taxonomy_fixtures:
    download:
      type: git
      url: 'https://github.com/NuCivic/taxonomy_fixtures.git'
      branch: 7.x-1.x
  token:
    version: '1.6'
  uuid:
    version: '1.0-beta2'
  views:
    version: '3.14'
    patch:
      1388684: 'https://www.drupal.org/files/views_taxonomy_entity_uri-1388684-15.patch'
  views_autocomplete_filters:
    version: '1.2'
    patch:
      2374709: 'http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch'
      2317351: 'http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch'
  views_bulk_operations:
    version: '3.3'
  views_responsive_grid:
    version: '1.3'
  visualization_entity:
    download:
      type: git
      url: https://github.com/NuCivic/visualization_entity.git
      tag: 7.x-1.0-beta2
    type: module
  workbench:
    version: '1.2'
  workbench_email:
    version: '3.11'
  workbench_moderation:
    download:
      type: git
      url: http://git.drupal.org/project/workbench_moderation.git
      branch: 7.x-1.x
      revision: 2c91211
    patch:
      2393771: 'https://www.drupal.org/files/issues/specify_change_state_user-2393771-5.patch'
      1838640: 'https://www.drupal.org/files/issues/workbench_moderation-fix_callback_argument-1838640-23.patch'
      2645622: 'https://www.drupal.org/files/issues/node-deleted-before-shutdown-function-2645622-4.patch'
libraries:
  chosen:
    download:
      type: get
      url: 'https://github.com/harvesthq/chosen/releases/download/v1.3.0/chosen_v1.3.0.zip'
  excanvas:
    download:
      type: git
      url: 'https://github.com/arv/ExplorerCanvas.git'
      sha1: aa989ea9d9bac748638f7c66b0fc88e619715da6
  font_awesome:
    type: libraries
    download:
      type: git
      url: 'https://github.com/FortAwesome/Font-Awesome.git'
      revision: 13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09
    directory_name: font_awesome  
  jquery.imagesloaded:
    download:
      type: file
      url: 'https://github.com/desandro/imagesloaded/archive/v2.1.2.tar.gz'
      subtree: imagesloaded-2.1.2
  jquery.imgareaselect:
    download:
      type: file
      url: 'https://github.com/odyniec/imgareaselect/archive/v0.9.11-rc.1.tar.gz'
      subtree: imgareaselect-0.9.11-rc.1
  slugify:
    download:
      type: git
      url: 'https://github.com/pmcelhaney/jQuery-Slugify-Plugin.git'
      revision: 79133a1bdfd3ac80d500d661a722b85c03a01da3
    directory_name: slugify
  spectrum:
    download:
      type: git
      url: 'https://github.com/bgrins/spectrum.git'
    directory_name: bgrins-spectrum
  spyc:
    download:
      type: file
      url: 'https://raw.github.com/mustangostang/spyc/master/Spyc.php'
    directory_name: spyc
defaults:
  projects:
    subdir: contrib
