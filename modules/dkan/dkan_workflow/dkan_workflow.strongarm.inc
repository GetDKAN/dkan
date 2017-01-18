<?php

/**
 * @file
 * dkan_workflow.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_workflow_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'menu_badges_tab_callbacks';
  $strongarm->value = array(
    'admin/workbench/drafts' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_my_drafts_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'my_drafts_badge',
      ),
    ),
    'admin/workbench/needs-review' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_needs_review_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'needs_review_badge',
      ),
    ),
    'admin/workbench/stale-drafts' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_stale_drafts_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'stale_drafts_badge',
      ),
    ),
    'admin/workbench/stale-reviews' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_stale_reviews_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'stale_reviews_badge',
      ),
    ),
    'admin/workbench/drafts-active' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_my_drafts_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'my_drafts_badge',
      ),
    ),
    'admin/workbench/drafts-stale' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_stale_drafts_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'stale_drafts_badge',
      ),
    ),
    'admin/workbench/needs-review-stale' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_stale_reviews_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'stale_reviews_badge',
      ),
    ),
    'admin/workbench/needs-review-active' => array(
      'callback' => 'menu_badges_execute_view',
      'key' => 'views_workbench_moderation_needs_review_badge',
      'arguments' => array(
        'view_name' => 'workbench_moderation',
        'view_display_id' => 'needs_review_badge',
      ),
    ),
  );
  $export['menu_badges_tab_callbacks'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'workbench_email_queue_mail';
  $strongarm->value = 0;
  $export['workbench_email_queue_mail'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'workbench_moderation_default_state_dataset';
  $strongarm->value = 'draft';
  $export['workbench_moderation_default_state_dataset'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'workbench_moderation_default_state_resource';
  $strongarm->value = 'draft';
  $export['workbench_moderation_default_state_resource'] = $strongarm;

  return $export;
}
