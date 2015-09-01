<?php
/**
 * @file
 * dkan_workflow_permissions.features.user_role.inc
 */

/**
 * Implements hook_user_default_roles().
 */
function dkan_workflow_permissions_user_default_roles() {
  $roles = array();

  // Exported role: Workflow Approver.
  $roles['Workflow Approver'] = array(
    'name' => 'Workflow Approver',
    'weight' => 8,
    'machine_name' => 'workflow_approver',
  );

  // Exported role: Workflow Creator.
  $roles['Workflow Creator'] = array(
    'name' => 'Workflow Creator',
    'weight' => 7,
    'machine_name' => 'workflow_creator',
  );

  // Exported role: Workflow Supervisor.
  $roles['Workflow Supervisor'] = array(
    'name' => 'Workflow Supervisor',
    'weight' => 9,
    'machine_name' => 'workflow_supervisor',
  );

  return $roles;
}
