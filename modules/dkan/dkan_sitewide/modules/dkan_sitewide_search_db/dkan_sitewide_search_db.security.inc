<?php

/**
 * @file
 * Code for the dkan_sitewide_search_db feature.
 */

/**
 * Helper for array_map to sanitize facet markup.
 */
function _filter_xss(&$facet) {
  $facet['#markup'] = filter_xss($facet['#markup']);
}

/**
 * Implements hook_facet_items_alter().
 */
function dkan_sitewide_search_db_facet_items_alter(&$build, &$settings) {
  // Apply filter xss to every facet in build.
  array_map('_filter_xss', $build);
}

/**
 * Cross references value with available node types.
 *
 * @param string $value
 *   Content type.
 *
 * @return bool
 *   Return bool to check if is a content type.
 */
function _facet_is_content_type($value) {
  $cts = node_type_get_types();
  $cts = array_keys($cts);
  return in_array($value, $cts);
}

/**
 * Cross reference value with term data.
 *
 * @param string $name
 *   Term name.
 * @param string $tid
 *   ID term.
 *
 * @return bool
 *   Return a bool to check if facet is a term.
 */
function _facet_is_term($name, $tid) {
  $query = db_select('taxonomy_term_data', 'terms')
    ->fields('terms', array('name'))
    ->condition('tid', $tid)
    ->execute();
  $result = $query->fetchObject();
  if ($query->rowCount() > 0) {
    $n1 = pathauto_cleanstring($result->name);
    $n2 = pathauto_cleanstring(urldecode($name));
    return $n1 == $n2;
  }
  return FALSE;
}

/**
 * Cross reference value with user data.
 */
function _facet_is_user($uid) {
  $result = db_select('users', 'users')
    ->fields('users', array('name'))
    ->condition('uid', $uid)
    ->execute();
  return $result->rowCount() > 0;
}

/**
 * Implements hook_menu_alter().
 */
function dkan_sitewide_search_db_menu_alter(&$items) {
  if (!empty($items['search'])) {
    $items['search']['page callback'] = '_alter_search_callback';
  }
}

/**
 * Wrapper callback for $items['search'].
 */
function _alter_search_callback($argument) {
  $path = explode('/', current_path());
  // Extra xss sanitation here just in case.
  $path = array_map('filter_xss', $path);

  // Validate CT's.
  if (in_array('type', $path)) {
    $n = array_search('type', $path);
    $type = $path[$n + 1];
    if (!_facet_is_content_type($type)) {
      return drupal_not_found();
    }
  }
  // Validate terms.
  $vocabs_to_validate = array(
    'field_tags' => 'tags',
    'field_topic' => 'dkan_topics',
    'field_resources%3Afield_format' => 'format',
  );
  foreach ($vocabs_to_validate as $key => $map) {
    $in_array = array_keys($path, $key);
    if (count($in_array)) {
      foreach ($in_array as $i) {
        // Check if a value is provided after the mark.
        if (!isset($path[$i + 1])) {
          return drupal_not_found();
        }
        $term = $path[$i + 1];
        $term = explode('-', $term);
        $last = array_pop($term);
        // Check if last element is a term id.
        if (!is_numeric($last)) {
          return drupal_not_found();
        }
        $term = array(implode('-', $term), $last);
        // Save the extra query if we can.
        if (count($term) == 1) {
          return drupal_not_found();
        }
        // Grab term and validate.
        if (!_facet_is_term($term[0], $term[1])) {
          return drupal_not_found();
        }
      }
    }
  }
  // Validate license field.
  $in_array = array_keys($path, 'field_license');
  if (count($in_array)) {
    $allowed_values = dkan_dataset_content_types_license_allowed_values();
    $allowed_values = array_keys($allowed_values);
    foreach ($in_array as $i) {
      // Check if a value is provided after the mark.
      if (!isset($path[$i + 1])) {
        return drupal_not_found();
      }
      $license = $path[$i + 1];
      if (!in_array($license, $allowed_values)) {
        return drupal_not_found();
      }
    }
  }
  // Validate author.
  $in_array = array_keys($path, 'author');
  if (count($in_array)) {
    foreach ($in_array as $i) {
      if (!isset($path[$i + 1])) {
        // Check if a value is provided after the mark.
        return drupal_not_found();
      }
      $uid = $path[$i + 1];
      // Check if uid is numeric.
      if (!is_numeric($uid)) {
        return drupal_not_found();
      }
      if (!_facet_is_user($uid)) {
        return drupal_not_found();
      }
    }
  }
  // If anything works as expected, call the original callback.
  return page_manager_page_execute($argument);
}
