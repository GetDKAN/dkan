<?php
/**
 * @file
 * Create random data to populate geofields.
 */

/**
 * Implements hook_devel_generate().
 */
function geofield_devel_generate($object, $field, $instance, $bundle) {
  if (field_behaviors_widget('multiple values', $instance) == FIELD_BEHAVIOR_CUSTOM) {
    return devel_generate_multiple('_geofield_devel_generate', $object, $field, $instance, $bundle);
  }
  else {
    return _geofield_devel_generate($object, $field, $instance, $bundle);
  }
}

function _geofield_devel_generate($object, $field, $instance, $bundle) {
  $object_field = array();
  $type = str_replace('geofield_', '', $instance['widget']['type']);
  $val = array(
    'wkt' => NULL,
    'lat' => NULL,
    'lon' => NULL,
    'top' => NULL,
    'bottom' => NULL,
    'right' => NULL,
    'left' => NULL,
  );
  if ($type == 'latlon' || $type == 'bounds') {
    list($val['lon'], $val['lat']) = _random_point();
    // don't actually need lat/lon but provide a center for our bounds
    if ($type == 'bounds') {
      $lat_diff = _dd_generate(2, 10) / 100;
      $lon_diff = _dd_generate(2, 10) / 100;
      $val['left']   = $val['lon'] - $lon_diff;
      $val['right']  = $val['lon'] + $lon_diff;
      $val['top']    = $val['lat'] - $lat_diff;
      $val['bottom'] = $val['lat'] + $lat_diff;
    }
  }
  else {
    $type = 'wkt';
    $val['wkt'] = _wkt_generate();
  }
  $values = geofield_compute_values($val, $type);
  return $values;
}

/**
 * Helper to generate DD coordinates
 */
function _dd_generate($min, $max, $int = FALSE) {
  $func = 'rand';
  if (function_exists('mt_rand')) {
    $func = 'mt_rand';
  }
  $number = $func($min, $max);
  if ($int || abs($number) === 180) {
    return $number;
  }
  $decimals = $func(1, pow(10, 5)) / pow(10, 5);
  return round($number + $decimals, 5);
}

/**
 * Helper to generate a random WKT string
 *
 * Try to keeps values sane, no shape is more than 100km across
 */
function _wkt_generate() {
  $types = array(
    'point',
    'linestring',
    'polygon',
    'multipoint',
    'multilinestring',
    'multipolygon',
  );
  // don't always generate the same type
  shuffle($types);
  $type = $types[0];
  $func = '_wkt_generate_' . $type;
  if (function_exists($func)) {
    $wkt = $func();
    return drupal_strtoupper($type) . ' (' . $wkt . ')';
  }
  return 'POINT (0 0)';
}

function _random_point() {
  $lon = _dd_generate(-180, 180);
  $lat = _dd_generate(-84, 84);
  return array($lon, $lat);
}

function _wkt_generate_point($point = FALSE) {
  $point = $point ? $point : _random_point();
  return implode(' ', $point);
}

function _wkt_generate_multipoint() {
  $num = _dd_generate(1, 5, TRUE);
  $start = _random_point();
  $points[] = _wkt_generate_point($start);
  for ($i = 0; $i < $num; $i += 1) {
    $diff = _random_point();
    $start[0] += $diff[0] / 100;
    $start[1] += $diff[1] / 100;
    $points[] = _wkt_generate_point($start);
  }
  return implode(', ', $points);
}

// make a line that looks like a line
function _wkt_generate_linestring($start = FALSE, $segments = FALSE) {
  $start = $start ? $start : _random_point();
  $segments = $segments ? $segments : _dd_generate(1, 5, TRUE);
  $points[] = $start[0] . ' ' . $start[1];
  // Points are at most 1km away from each other
  for ($i = 0; $i < $segments; $i += 1) {
    $diff = _random_point();
    $start[0] += $diff[0] / 100;
    $start[1] += $diff[1] / 100;
    $points[] = $start[0] . ' ' . $start[1];
  }
  return implode(", ", $points);
}

// make a line that looks like a line
function _wkt_generate_multilinestring() {
  $start = _random_point();
  $num = _dd_generate(1, 3, TRUE);
  $lines[] = _wkt_generate_linestring($start);
  for ($i = 0; $i < $num; $i += 1) {
    $diff = _random_point();
    $start[0] += $diff[0] / 100;
    $start[1] += $diff[1] / 100;
    $lines[] = _wkt_generate_linestring($start);
  }
  return '(' . implode('), (', $lines) . ')';
}

function _wkt_generate_polygon($start = FALSE, $segments = FALSE) {
  $start = $start ? $start : _random_point();
  $segments = $segments ? $segments : _dd_generate(2, 4, TRUE);
  $poly = _wkt_generate_linestring($start, $segments);
  // close the polygon
  return '(' . $poly . ' , ' . $start[0] . ' ' . $start[1] . ')';
}

function _wkt_generate_multipolygon() {
  $start = _random_point();
  $num = _dd_generate(1, 5, TRUE);
  $segments = _dd_generate(2, 3, TRUE);
  $poly[] = _wkt_generate_polygon($start, $segments);
  for ($i = 0; $i < $num; $i += 1) {
    $diff = _random_point();
    $start[0] += $diff[0] / 100;
    $start[1] += $diff[1] / 100;
    $poly[] = _wkt_generate_polygon($start, $segments);
  }
  return '(' . implode(', ', $poly) . ')';
}
