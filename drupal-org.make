---
api: '2'
core: 7.x
includes:
- https://raw.githubusercontent.com/NuCivic/dkan_dataset/7.x-1.12/dkan_dataset.make
- https://raw.githubusercontent.com/NuCivic/dkan_datastore/7.x-1.12/dkan_datastore.make
- https://raw.githubusercontent.com/NuCivic/dkan_workflow/7.x-1.12/dkan_workflow.make
- https://raw.githubusercontent.com/NuCivic/visualization_entity/a2490a0ea1baa8fe3617d669b4c9a1c8f0b5626c/visualization_entity.make
- https://raw.githubusercontent.com/NuCivic/visualization_entity_charts/d02f1b19797840b0d4b8b92625b4c22ca0a603f2/visualization_entity_charts.make
- modules/dkan/dkan_data_story/dkan_data_story.make
- modules/dkan/dkan_topics/dkan_topics.make
projects:
  manualcrop:
    version: 1.x-dev
    download:
      type: git
      revision: d6c449d
      branch: 7.x-1.x
  tablefield:
    version: '2.4'
  simple_gmap:
    version: '1.2'
  menu_block:
    version: '2.7'
  file_entity:
    version: 2.0-beta2
    patch:
      2308737: https://www.drupal.org/files/issues/file_entity-remove-field-status-check-2308737-9509141.patch
  media:
    version: 2.0-beta1
    patch:
      2126697: https://www.drupal.org/files/issues/media_wysiwyg_2126697-53.patch
      2308487: https://www.drupal.org/files/issues/media-alt-title-double-encoded-2308487-2.patch
      2084287: http://www.drupal.org/files/issues/media-file-name-focus-2084287-2.patch
      2534724: https://www.drupal.org/files/issues/media-browser_opens_twice-2534724-53.patch
  media_youtube:
    version: '3.0'
  media_vimeo:
    version: '2.1'
    patch:
      2446199: https://www.drupal.org/files/issues/no_exception_handling-2446199-1.patch
  dkan_dataset:
    subdir: dkan
    download:
      type: git
      url: https://github.com/NuCivic/dkan_dataset.git
      tag: 7.x-1.12
  dkan_datastore:
    subdir: dkan
    download:
      type: git
      url: https://github.com/NuCivic/dkan_datastore.git
      tag: 7.x-1.12
  dkan_workflow:
    subdir: dkan
    download:
      type: git
      url: https://github.com/NuCivic/dkan_workflow.git
      tag: 7.x-1.12
  visualization_entity:
    download:
      type: git
      url: https://github.com/NuCivic/visualization_entity.git
      revision: a2490a0ea1baa8fe3617d669b4c9a1c8f0b5626c
    type: module
  visualization_entity_charts:
    download:
      type: git
      url: https://github.com/NuCivic/visualization_entity_charts.git
      revision: d02f1b19797840b0d4b8b92625b4c22ca0a603f2
    type: module
  admin_menu:
    version: 3.0-rc5
  bueditor:
    version: '1.8'
    patch:
      1931862: http://drupal.org/files/dont-render-bueditor-for-plain-text-textareas.patch
  colorizer:
    version: '1.8'
    patch:
      2227651: https://www.drupal.org/files/issues/colorizer-add-rgb-vars-2227651-4b.patch
      2599298: https://www.drupal.org/files/issues/colorizer-bug_system_cron_delete_current_css-2599298-9.patch
  conditional_styles:
    version: '2.2'
  diff:
    version: '3.2'
  draggableviews:
    version: '2.1'
  entityreference_filter:
    version: '1.5'
  features_roles_permissions:
    version: '1.2'
  fieldable_panels_panes:
    version: '1.8'
  honeypot:
    version: '1.17'
  fontyourface:
    version: '2.8'
    patch:
      1: patches/fontyourface-no-ajax-browse-view.patch
      2: patches/fontyourface-clear-css-cache.patch
      2644694: https://www.drupal.org/files/issues/browse-fonts-page-uses-disabled-font-2644694.patch
  imagecache_actions:
    download:
      type: git
      url: http://git.drupal.org/project/imagecache_actions.git
      branch: 7.x-1.x
      revision: cd19d2a
    type: module
  markdown:
    version: '1.2'
  markdowneditor:
    version: '1.4'
    patch:
      2045225: http://drupal.org/files/remove-dsm-from-hook-install-2045225-1.patch
  module_filter:
    version: '2.0'
  og_moderation:
    version: '2.3'
  defaultconfig:
    version: 1.0-alpha11
  panelizer:
    version: '3.1'
  views_autocomplete_filters:
    version: '1.2'
    patch:
      2374709: http://www.drupal.org/files/issues/views_autocomplete_filters-cache-2374709-2.patch
      2317351: http://www.drupal.org/files/issues/views_autocomplete_filters-content-pane-2317351-4.patch
  panopoly_widgets:
    version: '1.25'
    patch:
      1: patches/panopoly_widgets_overrides.patch
      2: patches/panopoly_widgets_add_jquery_ui_tabs.patch
  panopoly_images:
    version: '1.27'
  panels:
    version: '3.5'
  panels_style_collapsible:
    version: '1.3'
  path_breadcrumbs:
    version: '3.3'
  pathauto:
    version: '1.2'
  radix_layouts:
    version: '3.4'
  r4032login:
    version: '1.8'
  rules:
    version: '2.3'
  restws:
    version: '2.3'
    patch:
      2484829: https://www.drupal.org/files/issues/restws-fix-format-extension-2484829-53.patch
  schema:
    version: '1.2'
  adminrole:
    version: '1.1'
  admin_menu_source:
    download:
      type: git
      url: http://git.drupal.org/project/admin_menu_source.git
      branch: 7.x-1.x
      revision: 8514d8b
    patch:
      2441283: https://www.drupal.org/files/issues/allow_ordering_of_the-2441283-5.patch
  menu_token:
    version: 1.0-beta5
  delta:
    version: 3.0-beta11
  omega:
    version: '3.1'
    patch:
      1828552: http://drupal.org/files/1828552-omega-hook_views_mini_pager.patch
    type: theme
  nuboot_radix:
    download:
      type: git
      url: https://github.com/NuCivic/nuboot_radix.git
      tag: 7.x-1.12
    type: theme
  radix:
    type: theme
    version: 3.3
  field_reference_delete:
    download:
      version: 7.x-1.0-beta1
  facetapi:
    patch:
      1: patches/cross-site-scripting-facets-156778.patch
libraries:
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
  font_awesome:
    type: libraries
    download:
      type: git
      url: https://github.com/FortAwesome/Font-Awesome.git
      revision: 13d5dd373cbf3f2bddd8ac2ee8df3a1966a62d09
    directory_name: font_awesome
  spyc:
    download:
      type: get
      url: https://raw.github.com/mustangostang/spyc/79f61969f63ee77e0d9460bc254a27a671b445f3/spyc.php
    filename: "../spyc.php"
    directory_name: lib
    destination: modules/contrib/services/servers/rest_server
defaults:
  projects:
    subdir: contrib
