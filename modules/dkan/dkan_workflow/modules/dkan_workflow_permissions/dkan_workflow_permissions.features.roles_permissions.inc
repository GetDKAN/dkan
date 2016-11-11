<?php

/**
 * @file
 * dkan_workflow_permissions.features.roles_permissions.inc
 */

/**
 * Implements hook_default_roles_permissions().
 */
function dkan_workflow_permissions_default_roles_permissions() {
  $roles = array();

  // Exported role: Workflow Moderator.
  $roles['Workflow Moderator'] = array(
    'name' => 'Workflow Moderator',
    'weight' => 8,
    'permissions' => array(
      'access administration menu' => TRUE,
      'access workbench' => TRUE,
      'moderate content from draft to needs_review' => TRUE,
      'moderate content from needs_review to draft' => TRUE,
      'moderate content from needs_review to published' => TRUE,
      'use workbench_moderation my drafts tab' => TRUE,
      'use workbench_moderation needs review tab' => TRUE,
      'view all unpublished content' => TRUE,
      'view moderation history' => TRUE,
      'view moderation messages' => TRUE,
    ),
  );

  // Exported role: Workflow Contributor.
  $roles['Workflow Contributor'] = array(
    'name' => 'Workflow Contributor',
    'weight' => 7,
    'permissions' => array(
      'access administration menu' => TRUE,
      'access workbench' => TRUE,
      'moderate content from draft to needs_review' => TRUE,
      'moderate content from needs_review to draft' => TRUE,
      'use workbench_moderation my drafts tab' => TRUE,
      'use workbench_moderation needs review tab' => TRUE,
      'view moderation history' => TRUE,
      'view moderation messages' => TRUE,
    ),
  );

  // Exported role: Workflow Supervisor.
  $roles['Workflow Supervisor'] = array(
    'name' => 'Workflow Supervisor',
    'weight' => 9,
    'permissions' => array(
      'access administration menu' => TRUE,
      'access stale drafts page' => TRUE,
      'access stale reviews page' => TRUE,
      'access workbench' => TRUE,
      'administer workbench' => TRUE,
      'administer workbench emails' => TRUE,
      'administer workbench moderation' => TRUE,
      'bypass workbench moderation' => TRUE,
      'moderate content from draft to needs_review' => TRUE,
      'moderate content from needs_review to draft' => TRUE,
      'moderate content from needs_review to published' => TRUE,
      'use workbench_moderation my drafts tab' => TRUE,
      'use workbench_moderation needs review tab' => TRUE,
      'view all unpublished content' => TRUE,
      'view moderation history' => TRUE,
      'view moderation messages' => TRUE,
    ),
  );

  return $roles;
}
