<?php
/**
 * @file
 * dkan_workflow.features.features_overrides.inc
 */

/**
 * Implements hook_features_override_default_overrides().
 */
function dkan_workflow_features_override_default_overrides() {
  // This code is only used for UI in features. Exported alters hooks do the magic.
  $overrides = array();

  // Exported overrides for: variable
  $overrides["variable.node_options_dataset.value|0"] = 'moderation';
  $overrides["variable.node_options_resource.value|0"] = 'moderation';

 return $overrides;
}
