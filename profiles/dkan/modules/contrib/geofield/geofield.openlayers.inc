<?php

/**
 * @file
 * Provides hooks for integration with OpenLayers (http://drupal.org/project/openlayers)
 */

/**
 * Implements hook_openlayers_maps().
 */
function geofield_openlayers_maps() {
  // Create full preset array
  $widget = new stdClass;
  $widget->disabled = FALSE; /* Edit this to true to make a default openlayers_maps disabled initially */
  $widget->api_version = 1;
  $widget->name = 'geofield_widget_map';
  $widget->title = 'Geofield Widget Map';
  $widget->description = 'A Map Used for Geofield Input';
  $widget->data = array(
    'width' => '600px',
    'height' => '400px',
    'image_path' => drupal_get_path('module', 'openlayers') . '/themes/default_dark/img/',
    'css_path' => drupal_get_path('module', 'openlayers') . '/themes/default_dark/style.css',
    'proxy_host' => '',
    'hide_empty_map' => 0,
      'center' => array(
        'initial' => array(
        'centerpoint' => '0, 0',
        'zoom' => '1',
      ),
      'restrict' => array(
        'restrictextent' => 0,
        'restrictedExtent' => '',
      ),
    ),
    'behaviors' => array(
      'openlayers_behavior_geofield' => array(
        'feature_types' => array(
          'point' => 'point',
          'path' => 'path',
          'polygon' => 'polygon',
        ),
        'allow_edit' => 1,
      ),
      'openlayers_behavior_keyboarddefaults' => array(),
      'openlayers_behavior_navigation' => array(
        'zoomWheelEnabled' => 0,
        'zoomBoxEnabled' => 1,
        'documentDrag' => 0,
      ),
      'openlayers_behavior_panzoombar' => array(
        'zoomWorldIcon' => 0,
        'panIcons' => 1,
      ),
    ),
    'default_layer' => 'mapquest_osm',
    'layers' => array(
      'mapquest_osm' => 'mapquest_osm',
    ),
    'layer_weight' => array(
      'openlayers_geojson_picture_this' => '0',
      'geofield_formatter' => '0',
    ),
    'layer_styles' => array(
      'geofield_formatter' => '0',
      'openlayers_geojson_picture_this' => '0',
    ),
    'layer_styles_select' => array(
      'geofield_formatter' => '0',
      'openlayers_geojson_picture_this' => '0',
    ),
    'layer_activated' => array(
      'geofield_formatter' => 0,
      'openlayers_geojson_picture_this' => 0,
    ),
    'layer_switcher' => array(
      'geofield_formatter' => 0,
      'openlayers_geojson_picture_this' => 0,
    ),
    'projection' => 'EPSG:900913',
    'displayProjection' => 'EPSG:4326',
    'styles' => array(
      'default' => 'default',
      'select' => 'default',
      'temporary' => 'default',
    ),
    'map_name' => 'geofield_widget_map',
  );

  $formatter = new stdClass();
  $formatter->api_version = 1;
  $formatter->name = 'geofield_formatter_map';
  $formatter->title = t('Geofield Formatter Map');
  $formatter->description = t('A Map Used for Geofield Output');
  $formatter->data = array(
    'width' => '600px',
    'height' => '400px',
    'image_path' => drupal_get_path('module', 'openlayers') . '/themes/default_dark/img/',
    'css_path' => drupal_get_path('module', 'openlayers') . '/themes/default_dark/style.css',
    'proxy_host' => '',
    'hide_empty_map' => 0,
    'center' => array(
      'initial' => array(
        'centerpoint' => '0, 0',
        'zoom' => '1',
      ),
      'restrict' => array(
        'restrictextent' => 0,
        'restrictedExtent' => '',
      ),
    ),
    'behaviors' => array(
      'openlayers_behavior_keyboarddefaults' => array(),
      'openlayers_behavior_navigation' => array(
        'zoomWheelEnabled' => 0,
        'zoomBoxEnabled' => 1,
        'documentDrag' => 0,
      ),
      'openlayers_behavior_panzoombar' => array(
        'zoomWorldIcon' => 0,
        'panIcons' => 1,
      ),
    ),
    'default_layer' => 'mapquest_osm',
    'layers' => array(
      'mapquest_osm' => 'mapquest_osm',
      'geofield_formatter' => 'geofield_formatter',
    ),
    'layer_weight' => array(
      'geofield_formatter' => '0',
      'openlayers_geojson_picture_this' => '0',
    ),
    'layer_styles' => array(
      'openlayers_geojson_picture_this' => '0',
      'geofield_formatter' => '0',
    ),
    'layer_styles_select' => array(
      'openlayers_geojson_picture_this' => '0',
      'geofield_formatter' => '0',
    ),
    'layer_activated' => array(
      'geofield_formatter' => 'geofield_formatter',
      'openlayers_geojson_picture_this' => 0,
    ),
    'layer_switcher' => array(
      'geofield_formatter' => 0,
      'openlayers_geojson_picture_this' => 0,
    ),
    'projection' => 'EPSG:900913',
    'displayProjection' => 'EPSG:4326',
    'styles' => array(
      'default' => 'default',
      'select' => 'default',
      'temporary' => 'default',
    ),
    'map_name' => 'geofield_formatter_map',
  );

  return array(
    'geofield_widget_map' => $widget,
    'geofield_formatter_map' => $formatter,
  );
}

/**
 * Implements hook_openlayers_behaviors().
 */
function geofield_openlayers_behaviors() {
  return array(
    'openlayers_behavior_geofield' => array(
      'title' => t('Geofield'),
      'description' => t('Fuels the geofield map-input form.'),
      'type' => 'layer',
      'behavior' => array(
        'path' => drupal_get_path('module', 'geofield') . '/includes/behaviors',
        'file' => 'openlayers_behavior_geofield.inc',
        'class' => 'openlayers_behavior_geofield',
        'parent' => 'openlayers_behavior',
      ),
    ),
  );
}

/**
 * Formatter layers
 */
function geofield_openlayers_layers() {
  $layers = array();
  $layer = new stdClass();
  $layer->api_version = 1;
  $layer->name = 'geofield_formatter';
  $layer->title = 'Placeholder for Geofield Formatter';
  $layer->description = '';
  $layer->data = array(
    'layer_type' => 'openlayers_layer_type_raw',
    'projection' => array('EPSG:900913'),
    'features' => array()
  );
  $layers[$layer->name] = $layer;
  return $layers;
}
