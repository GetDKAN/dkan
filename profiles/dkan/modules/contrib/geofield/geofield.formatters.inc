<?php

/**
 * @file
 * Drupal field formatter hooks and helper functions.
 */

/**
 * Implements hook_field_formatter_info().
 */
function geofield_field_formatter_info() {
  $formatters = array(
    'geofield_wkt' => array(
      'label' => t('Well Known Text (WKT)'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full'),
    ),
    'geofield_geojson' => array(
      'label' => t('GeoJSON'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full'),
    ),
    'geofield_kml' => array(
      'label' => t('KML'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full'),
    ),
    'geofield_gpx' => array(
      'label' => t('GPX'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full'),
    ),
    'geofield_latlon' => array(
      'label' => t('Latitude/Longitude'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full', 'format' => 'decimal_degrees', 'labels' => 1),
    ),
    'geofield_lat' => array(
      'label' => t('Latitude Only'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full', 'format' => 'decimal_degrees'),
    ),
    'geofield_lon' => array(
      'label' => t('Longitude Only'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full', 'format' => 'decimal_degrees'),
    ),
    'geofield_geo_type' => array(
      'label' => t('Geometry Type'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full'),
    ),
  );

  if (module_exists('openlayers')) {
    $formatters['geofield_openlayers'] = array(
      'label' => t('OpenLayers'),
      'field types' => array('geofield'),
      'settings' => array('data' => 'full', 'map_preset' => 'geofield_formatter_map'),
    );
  }

  // Accessibility formatters for blind users with screen-readers
  $formatters['geofield_def_list'] = array(
    'label' => t('Definition List (Accessibility)'),
    'field types' => array('geofield'),
    'settings' => array('data' => '', 'address' => 0),
  );
  $formatters['geofield_description'] = array(
    'label' => t('Decriptive Text (Accessibility)'),
    'field types' => array('geofield'),
    'settings' => array('data' => '', 'address' => 0),
  );

  return $formatters;
}

/**
 * Helper function for getting data options
 */
function _geofield_formatter_settings_data_options($formatter) {
  return array(
    'full' => t('Use full geometry'),
    'centroid' => t('Use centroid'),
    'bounding' => t('Use bounding box'),
  );
}

/**
 * Helper function for getting format options
 */
function _geofield_formatter_settings_format_options($formatter) {
  return array(
    'decimal_degrees' => t('Decimal degrees'),
    'degrees_minutes_seconds' => t('Degrees minutes seconds'),
    'ccs' => t('Astronomical Celestial Coordinates System (CCS)'),
  );
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function geofield_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  // Map preset formatter
  if ($display['type'] == 'geofield_openlayers' && module_exists('openlayers')) {
    // Get preset options, filtered to those which have the GeoField placeholder layer
    $presets = openlayers_presets();
    $preset_options = array();
    foreach ($presets as $preset) {
      if (in_array('geofield_formatter', $preset->data['layers'])) {
        $preset_options[$preset->name] = $preset->title;
      }
    }

    $element['map_preset'] = array(
      '#title' => t('OpenLayers Preset'),
      '#type' => 'select',
      '#default_value' => $settings['map_preset'] ? $settings['map_preset'] : 'geofield_formatter_map',
      '#required' => TRUE,
      '#options' => $preset_options,
      '#description' => t('Select which OpenLayers map you would like to use. Only maps which have the geofield placeholder layer may be selected. If your preferred map is not here, add the geofield placeholder layer to it first.'),
    );
  }

  $data_options = _geofield_formatter_settings_data_options($display['type']);
  $element['data'] = array(
    '#title' => t('Data options'),
    '#type' => 'select',
    '#default_value' => $settings['data'] ? $settings['data'] : 'full',
    '#required' => TRUE,
    '#options' => $data_options,
  );

  if ($display['type'] == 'geofield_latlon' || $display['type'] == 'geofield_lat' || $display['type'] == 'geofield_lon') {
    $format_options = _geofield_formatter_settings_format_options($display['type']);
    $element['format'] = array(
      '#title' => t('Format'),
      '#type' => 'select',
      '#default_value' => $settings['format'] ? $settings['format'] : 'decimal_degrees',
      '#required' => TRUE,
      '#options' => $format_options,
    );
  }

  if ($display['type'] == 'geofield_latlon') {
    $element['labels'] = array(
      '#title' => t('Display Labels'),
      '#type' => 'checkbox',
      '#default_value' => $settings['labels'],
    );
  }

  if ($display['type'] == 'geofield_def_list' || $display['type'] == 'geofield_description') {
    $element['address'] = array(
      '#title' => t('Include reverse-geocoded address'),
      '#type' => 'checkbox',
      '#default_value' => $settings['address'],
    );
  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function geofield_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $summary = array();

  $data_options = _geofield_formatter_settings_data_options($display['type']);

  // Styles could be lost because of enabled/disabled modules that defines
  // their styles in code.
  if (!empty($data_options[$settings['data']])) {
    $summary[] = t('Data options: @data', array('@data' => $data_options[$settings['data']]));
  }
  else {
    $summary[] = t('No data options set');
  }

  if ($display['type'] == 'geofield_openlayers' && !empty($settings['map_preset'])) {
    $openlayers_presets = openlayers_preset_options();
    $summary[] = t('Openlayers Map: @data', array('@data' => $openlayers_presets[$settings['map_preset']]));
  }

  if ($display['type'] == 'geofield_latlon') {
    $format_options = _geofield_formatter_settings_format_options($display['type']);
    // Display this setting only if image is linked.
    if (isset($format_options[$settings['format']])) {
      $summary[] = $format_options[$settings['format']];
    }
  }

  if (!empty($settings['address']) && $settings['address']) {
    $summary[] = t('Including reverse-geocoded address');
  }

  return implode('<br />', $summary);
}

/**
 * Implements hook_field_formatter_view().
 */
function geofield_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $element = array();

  // Transform into centroid or bounding if needed
  if ($display['settings']['data'] != 'full') {
    geophp_load();
    if ($display['settings']['data'] == 'centroid') {
      foreach ($items as $delta => $item) {
        $centroid_wkt = 'POINT(' . $item['lon'] . ' ' . $item['lat'] . ')';
        $centroid = geoPHP::load($centroid_wkt, 'wkt');
        $items[$delta] = geofield_get_values_from_geometry($centroid);
      }
    }
    if ($display['settings']['data'] == 'bounding') {
      foreach ($items as $delta => $item) {
        $envelope_wkt = 'POLYGON ((' . $item['left'] . ' ' . $item['top'] . ', ' . $item['right'] . ' ' . $item['top'] . ', ' . $item['right'] . ' ' . $item['bottom'] . ', ' . $item['left'] . ' ' . $item['bottom'] . ', ' . $item['left'] . ' ' . $item['top'] . ')))';
        $envelope = geoPHP::load($envelope_wkt, 'wkt');
        $items[$delta] = geofield_get_values_from_geometry($envelope);
      }
    }
  }

  // If we are a lat, lon, or latlon, and we are using degrees-minutes-seconds (instead of decimal degrees), then do a transformation
  if (isset($display['settings']['format'])) {
    if ($display['settings']['format'] == 'degrees_minutes_seconds') {
      foreach ($items as $delta => $item) {
        $items[$delta]['lat'] = geofield_latlon_DECtoDMS($item['lat'], 'lat');
        $items[$delta]['lon'] = geofield_latlon_DECtoDMS($item['lon'], 'lon');
      }
    }
  }

  // If we are a lat, lon, or latlon, and we are using celestial coordinate system (instead of decimal degrees), then do a transformation
  if (isset($display['settings']['format'])) {
    if ($display['settings']['format'] == 'ccs') {
      foreach ($items as $delta => $item) {
        $items[$delta]['lat'] = geofield_latlon_DECtoCCS($item['lat'], 'lat');
        $items[$delta]['lon'] = geofield_latlon_DECtoCCS($item['lon'], 'lon');
      }
    }
  }

  switch ($display['type']) {
    case 'geofield_wkt':
      foreach ($items as $delta => $item) {
        $element[$delta] = array('#markup' => $item['wkt']);
      }
      return $element;
    case 'geofield_geojson':
      geophp_load();
      foreach ($items as $delta => $item) {
        $geometry = geoPHP::load($item['wkt'], 'wkt');
        $json = $geometry->out('json');
        $element[$delta] = array('#markup' => $json);
      }
      return $element;
    case 'geofield_kml':
      geophp_load();
      foreach ($items as $delta => $item) {
        $geometry = geoPHP::load($item['wkt'], 'wkt');
        $kml = $geometry->out('kml');
        $element[$delta] = array('#markup' => $kml);
      }
      return $element;
    case 'geofield_gpx':
      geophp_load();
      foreach ($items as $delta => $item) {
        $geometry = geoPHP::load($item['wkt'], 'wkt');
        $kml = $geometry->out('gpx');
        $element[$delta] = array('#markup' => $kml);
      }
      return $element;
    case 'geofield_latlon':
      foreach ($items as $delta => $item) {
        if ($display['settings']['labels']) {
          $output = t('Latitude: !latitude <br/>Longitude: !longitude', array('!latitude' => $item['lat'], '!longitude' => $item['lon']));
        }
        else {
          $output = $item['lat'] . ', ' . $item['lon'];
        }
        $element[$delta] = array('#markup' => $output);
      }
      return $element;
    case 'geofield_lat':
      foreach ($items as $delta => $item) {
        $element[$delta] = array('#markup' => $item['lat']);
      }
      return $element;
    case 'geofield_lon':
      foreach ($items as $delta => $item) {
        $element[$delta] = array('#markup' => $item['lon']);
      }
      return $element;
    case 'geofield_geo_type':
      foreach ($items as $delta => $item) {
        $element[$delta] = array('#markup' => $item['geo_type']);
      }
      return $element;
    case 'geofield_openlayers':
      $map_name = $display['settings']['map_preset'] ? $display['settings']['map_preset'] : 'geofield_formatter_map';
      $element[0] = array('#markup' => _geofield_openlayers_formatter($map_name, $items));
      return $element;
    case 'geofield_def_list':
      foreach ($items as $delta => $item) {
        $element[$delta] = array('#markup' => _geofield_def_list_formatter($item, $display['settings']));
      }
      return $element;
    case 'geofield_description':
      foreach ($items as $delta => $item) {
        $element[$delta] = array('#markup' => _geofield_description_formatter($item, $display['settings']));
      }
      return $element;
  }

  return $element;
}

function _geofield_openlayers_formatter($map_name, $items) {
  $features = array();

  // Create array of $features
  foreach ($items as $delta) {
    $features[] = array(
      'wkt' => $delta['wkt'],
      'projection' => '4326',
    );
  }

  // Get map preset
  $preset = openlayers_preset_load($map_name);
  $map = openlayers_build_map($preset->data);

  if (!isset($map['layers']['geofield_formatter'])) {
    drupal_set_message(t('Trying to render a geofield formatter on a map without the placeholder layer'), 'error');
  }

  // Add the features to the placeholder layer
  $map['layers']['geofield_formatter']['features'] = $features;

  // Return themed map if no errors found
  if (empty($map['errors'])) {
    $js = array('openlayers' => array('maps' => array($map['id'] => $map)));
    drupal_add_js($js, 'setting');

    // Push map through theme function and return
    $output = theme('openlayers_map', array(
      'map' => $map,
      'map_name' => $map_name
    ));
  }
  return $output;
}

function _geofield_def_list_formatter($item, $settings) {
  geophp_load();
  $geometry = geoPHP::load($item['wkt'], 'wkt');

  // Single types
  $single_types = array('Point', 'LineStrnig', 'Polygon');

  if (in_array($geometry->geometryType(), $single_types)) {
    $centroid = new Point($item['lon'], $item['lat']);
    $info = _geofield_formatter_get_info($geometry, $centroid, $settings['address']);
    return _geofield_def_list_formatter_dl($info);
  }
  else {
    $output = '';
    foreach ($geometry->getComponents() as $component) {
      $centroid = $component->centroid();
      $info = _geofield_formatter_get_info($component, $centroid, $settings['address']);
      $output .= _geofield_def_list_formatter_dl($info);
    }
    return $output;
  }

}

function _geofield_def_list_formatter_dl($info) {
  $output = '<dl>';
  foreach ($info as $dt => $dd) {
    $output .= '<dt>' . drupal_ucfirst($dt) . '</dt><dd>' . $dd . '</dd>';
  }

  $output .= '</dl>';

  return $output;
}

function _geofield_description_formatter($item, $settings) {
  geophp_load();
  $geometry = geoPHP::load($item['wkt'], 'wkt');

  // Single types
  $single_types = array('Point', 'LineString', 'Polygon');

  if (in_array($geometry->geometryType(), $single_types)) {
    $centroid = new Point($item['lon'], $item['lat']);
    $info = _geofield_formatter_get_info($geometry, $centroid, $settings['address']);
    return _geofield_description_formatter_text($info);
  }
  else {
    $output = t('A collection of shapes');
    $output .= '<ol>';
    foreach ($geometry->getComponents() as $component) {
      $centroid = $component->centroid();
      $info = _geofield_formatter_get_info($component, $centroid, $settings['address']);
      $output .= '<li>' . _geofield_description_formatter_text($info) . '</li>';
    }
    $output .= '</ol>';
    return $output;
  }

}

function _geofield_description_formatter_text($info) {
  $text = t('@shape with a center of latitude @lat and longitude @lon.', array('@shape' => $info['shape'], '@lat' => $info['latitude'], '@lon' => $info['longitude']));
  if (isset($info['address'])) {
    $text .= ' ' . t('It has an approximate address of @address.', array('@address' => $info['address']));
  }

  if (isset($info['area'])) {
    $text .= ' ' . t('It has an area of @area.', array('@area' => $info['area']));
  }
  if (isset($info['length'])) {
    $text .= ' ' . t('It has a length of @length.', array('@length' => $info['length']));
  }
  return $text;
}

function _geofield_formatter_get_info($geometry, $centroid, $address = FALSE, $units = 'degrees') {

  $info = array(
    'latitude' => $centroid->y(),
    'longitude' => $centroid->x(),
  );

  // Get the shape
  if ($geometry->geometryType() == 'Point') {
    $info['shape'] = 'Point';
  }
  if ($geometry->geometryType() == 'LineString') {
    $info['shape'] = 'Line';
  }
  if ($geometry->geometryType() == 'Polygon') {
    //@@TODO: Get more useful information like 'Triangle', 'Square', 'Rectangle', 'Pentagon'
    $info['shape'] = 'Polygon';
  }

  if ($address) {
    $info['address'] = $centroid->out('google_geocode');
  }

  //@@TODO, convert length and area to proper units
  #if ($geometry->geometryType() == 'LineString') {
  #  $info['length'] = $geometry->length();
  #}
  #if ($geometry->geometryType() == 'Polygon') {
  #  $info['area'] = $geometry->area();
  #}

  return $info;
}
