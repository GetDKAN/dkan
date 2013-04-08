<?php

/**
 * @file
 * Provides support for the Views module.
 */

/**
 * Implements hook_views_data()
 */
function og_ui_views_data() {
  $data = array();

  // Define a handler for an area used to provide a group overview.
  $data['og_membership']['og_membership_overview'] = array(
    'title' => t('Group membership overview'),
    'help' => t('Overview of the group memberships (e.g. group manager, total memebrs).'),
    'area' => array(
      'handler' => 'og_ui_handler_area_og_membership_overview',
    ),
  );

  return $data;
}
