<?php

/**
 * @file
 * dkan_workflow.features.workbench_moderation_transitions.inc
 */

/**
 * Implements hook_workbench_moderation_export_transitions().
 */
function dkan_workflow_workbench_moderation_export_transitions() {
  $items = array(
    'draft:needs_review' => array(
      'from_name' => 'draft',
      'to_name' => 'needs_review',
      'name' => 'Submit for Review',
    ),
    'needs_review:draft' => array(
      'from_name' => 'needs_review',
      'to_name' => 'draft',
      'name' => 'Reject',
    ),
    'needs_review:published' => array(
      'from_name' => 'needs_review',
      'to_name' => 'published',
      'name' => 'Publish',
    ),
    'published:needs_review' => array(
      'from_name' => 'published',
      'to_name' => 'needs_review',
      'name' => 'Unpublish',
    ),
  );
  return $items;
}
