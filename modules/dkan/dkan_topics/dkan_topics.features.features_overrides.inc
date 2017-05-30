<?php

/**
 * @file
 * dkan_topics.features.features_overrides.inc
 */

/**
 * Implements hook_features_override_default_overrides().
 */
function dkan_topics_features_override_default_overrides() {
  // This code is only used for UI in features.
  // Exported alters hooks do the magic.
  $overrides = array();

  // Exported overrides for: page_manager_pages.
  $overrides["page_manager_pages.datasets.default_handlers|page_datasets_panel_context|conf|display|panels|sidebar|0"] = 'new-e73f5821-fb8b-44c1-9738-7217986ac3df';

  return $overrides;
}
