<?php

/**
 * @file
 * Feeds integration for Double Field module.
 */

/**
 * Implements hook_feeds_processor_targets_alter().
 *
 * @see FeedsNodeProcessor::getMappingTargets()
 */
function double_field_feeds_processor_targets_alter(&$targets, $entity_type, $bundle_name) {
  foreach (field_info_instances($entity_type, $bundle_name) as $name => $instance) {
    $info = field_info_field($name);
    if ($info['type'] == 'double_field') {
      foreach ($info['columns'] as $subfield => $column) {
        $targets["$name:$subfield"] = array(
          'name' => $instance['label'] . ':' . $subfield,
          'callback' => 'double_field_feeds_set_target',
          'description' => t(ucfirst($subfield) . ' subfield of the @name field.', array('@name' => $instance['label'])),
          'real_target' => $name,
        );
      }
    }
  }
}

/**
 * Callback for mapping.
 */
function double_field_feeds_set_target($source, $entity, $target, $value) {
  if (empty($value)) {
    return;
  }
  // Handle non-multiple value fields.
  $value = is_array($value) ? $value : array($value);
  list($field_name, $subfield) = explode(':', $target);
  $info = field_info_field($field_name);
  // Iterate over all values.
  foreach ($value as $delta => $item) {
    if (!is_array($item) && !is_object($item)) {
      $entity->{$field_name}[LANGUAGE_NONE][$delta][$subfield] = $item;
    }
  }
}
