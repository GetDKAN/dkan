<?php

/**
 * @file
 * On behalf implementation of Feeds mapping API for file.module and
 * image.module.
 *
 * Does actually not include mappers for field types defined in fields module
 * (because there aren't any) but mappers for all fields that contain their
 * value simply in $entity->fieldname['und'][$i]['value'].
 */

/**
 * Implements hook_feeds_processor_targets_alter().
 *
 * @see FeedsNodeProcessor::getMappingTargets().
 */
function file_feeds_processor_targets_alter(&$targets, $entity_type, $bundle_name) {
  foreach (field_info_instances($entity_type, $bundle_name) as $name => $instance) {
    $info = field_info_field($name);

    if (in_array($info['type'], array('file', 'image'))) {
      $targets[$name] = array(
        'name' => check_plain($instance['label']),
        'callback' => 'file_feeds_set_target',
        'description' => t('The @label field of the node.', array('@label' => $instance['label'])),
      );
    }
  }
}

/**
 * Callback for mapping. Here is where the actual mapping happens.
 *
 * When the callback is invoked, $target contains the name of the field the
 * user has decided to map to and $value contains the value of the feed item
 * element the user has picked as a source.
 */
function file_feeds_set_target($source, $entity, $target, $value) {
  if (empty($value)) {
    return;
  }
  module_load_include('inc', 'file');

  // Make sure $value is an array of objects of type FeedsEnclosure.
  if (!is_array($value)) {
    $value = array($value);
  }
  foreach ($value as $k => $v) {
    if (!($v instanceof FeedsEnclosure)) {
      if (is_string($v)) {
        $value[$k] = new FeedsEnclosure($v, file_get_mimetype($v));
      }
      else {
        unset($value[$k]);
      }
    }
  }
  if (empty($value)) {
    return;
  }

  $entity_type = $source->importer->processor->entityType();

  // Determine file destination.
  // @todo This needs review and debugging.
  $instance_info = field_info_instance($entity_type, $target, $source->importer->processor->bundle());
  $info = field_info_field($target);
  $data = array();
  if (!empty($entity->uid)) {
    $data[$entity_type] = $entity;
  }
  $destination = file_field_widget_uri($info, $instance_info, $data);

  // Populate entity.
  $i = 0;
  $field = isset($entity->$target) ? $entity->$target : array();
  foreach ($value as $v) {
    try {
      $file = $v->getFile($destination);
    }
    catch (Exception $e) {
      watchdog_exception('Feeds', $e, nl2br(check_plain($e)));
    }
    if ($file) {
      $field['und'][$i] = (array)$file;
      $field['und'][$i]['display'] = 1; // @todo: Figure out how to properly populate this field.
      if ($info['cardinality'] == 1) {
        break;
      }
      $i++;
    }
  }
  $entity->{$target} = $field;
}
