<?php

/**
 * @file
 * On behalf implementation of Feeds mapping API for user profiles.
 */

/**
 * Implements hook_feeds_processor_target_alter().
 */
function profile_feeds_processor_targets_alter(&$targets, $entity_type, $bundle_name) {

  if ($entity_type != 'user') {
    return;
  }

  $categories = profile_user_categories();

  foreach ($categories as $category) {
    foreach (_profile_get_fields($category['name']) as $record) {
      $targets[$record->name] = array(
        'name' => t('Profile: @name', array('@name' => $record->title)),
        'description' => t('Profile: @name', array('@name' => $record->title)),
        'callback' => 'profile_feeds_set_target',
      );
    }
  }
}

/**
 * Set the user profile target after import.
 */
function profile_feeds_set_target($source, $entity, $target, $value, $mapping) {
  $entity->$target = $value;
}
