api: '2'
core: 7.x
includes:
  - "https://raw.githubusercontent.com/NuCivic/dkan_dataset/7.x-1.x/dkan_dataset.make"
  - "https://raw.githubusercontent.com/NuCivic/visualization_entity/7.x-1.0-beta1/visualization_entity.make"
  - "https://raw.githubusercontent.com/NuCivic/open_data_schema_map/7.x-1.x/open_data_schema_map.make"
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
  bueditor:
    version: '1.8'
  bueditor_plus:
    version: '1.4'
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
  data:
    version: '1.x'
  defaultconfig:
    version: '1.0-alpha11'
  diff:
    version: '3.2'
  dkan_dataset:
    subdir: dkan
    download:
      type: git
      url: 'https://github.com/NuCivic/dkan_dataset.git'
      branch: 7.x-1.x
  draggableviews:
    version: '2.1'
  entity_path:
    version: '1.x-dev'
    patch:
      2809655: 'https://www.drupal.org/files/issues/entity-path-mysql-5-7_1.diff'
  entityreference_filter:
    version: '1.5'
  facetapi:
    patch:
      1: patches/cross-site-scripting-facets-156778.patch
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
  globalredirect:
    version: '1.5'
  honeypot:
    version: '1.22'
  image_url_formatter:
    version: '1.4'
  imagecache_actions:
    version: '1.7'
    type: module
  job_scheduler:
    version: '2.x'
  link_badges:
    version: '1.1'
  manualcrop:
    version: '1.5'
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
  menu_badges:
    version: '1.2'
  menu_block:
    version: '2.7'
  menu_token:
    version: '1.0-beta5'
  migrate:
    version: '2.8'
  migrate_extras:
    version: '2.5'
  module_filter:
    version: '2.0'
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
  role_export:
    version: '1.0'
  rules:
    version: '2.9'
  restws:
    version: '2.6'
  roleassign:
    version: '1.1'
  schema:
    version: '1.2'
    revision: 08b02458694d186f8ab3bd0b24fbc738f9271108
  services:
    version: '3.16'
  simple_gmap:
    version: '1.2'  
  tablefield:
    version: '2.4'
  taxonomy_menu:
    version: '1.5'
  taxonomy_fixtures:
    download:
      type: git
      url: 'https://github.com/NuCivic/taxonomy_fixtures.git'
      branch: 7.x-1.x
  views:
    patch:
      1388684: 'https://www.drupal.org/files/views_taxonomy_entity_uri-1388684-15.patch'
  views_autocomplete_filters:
    version: '1.2'
    patch:
      2374709: 'http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch'
      2317351: 'http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch'
  visualization_entity:
    download:
      type: git
      url: https://github.com/NuCivic/visualization_entity.git
      tag: 7.x-1.0-beta1
    type: module
  workbench:
    version: '1.2'
  workbench_email:
    version: '3.9'
    patch:
      2391233: 'https://www.drupal.org/files/issues/workbench_email-2391233-3.patch'
  workbench_moderation:
    branch: 7.x-1.x
    revision: 2c91211
    patch:
      2393771: 'https://www.drupal.org/files/issues/specify_change_state_user-2393771-5.patch'
      1838640: 'https://www.drupal.org/files/issues/workbench_moderation-fix_callback_argument-1838640-23.patch'
libraries:
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
  font_awesome:
    type: libraries
    download:
      type: git
      url: 'https://github.com/FortAwesome/Font-Awesome.git'
      revision: 13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09
    directory_name: font_awesome
  spectrum:
    download:
      type: git
      url: 'https://github.com/bgrins/spectrum.git'
      directory_name: bgrins-spectrum
  spyc:
    download:
      type: get
      url: 'https://raw.github.com/mustangostang/spyc/79f61969f63ee77e0d9460bc254a27a671b445f3/spyc.php'
    filename: ../spyc.php
    directory_name: lib
    destination: modules/contrib/services/servers/rest_server
defaults:
  projects:
    subdir: contrib
