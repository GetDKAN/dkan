<?php

/**
 * @file
 * Deploy hooks for the metastore module.
 */

/**
 * Update the dkan_json_form_widget field widget settings.
 */
function metastore_deploy_1001(&$sandbox) {
  // Retrieve the entity form display settings for the 'data' content type.
  $entity_form_display = \Drupal::service('config.factory')
    ->getEditable('core.entity_form_display.node.data.default');
  // Update the 'dkan_json_form_widget' field widget settings.
  $settings = $entity_form_display->get('content.field_json_metadata');
  if (empty($settings)) {
    return "No settings found for 'field_json_metadata'.";
  }
  if ($settings['type'] == 'json_form_widget') {
    $settings['type'] = 'dkan_json_form_widget';
    $entity_form_display->set('content.field_json_metadata', $settings)->save(TRUE);
    return "Updated 'field_json_metadata' to use 'dkan_json_form_widget'.";
  }
  return "No changes were necessary. Nothing was updated.";
}
