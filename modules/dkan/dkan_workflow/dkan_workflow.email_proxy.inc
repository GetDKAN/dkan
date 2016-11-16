<?php

/**
 * @file
 * Implements a transition email proxy for dkan_workflow.
 *
 * The business logic we wish to capture:
 * Allow email send if the user is the origional author of the node.
 *
 * Only allow emails to "Worflow Moderator" that are in the same group as the
 * node.
 *
 * If the node does not belong to a group, then only allow emails to users
 * that belong to the "Workflow Supervisor" role.
 *
 * If the node belongs to a group do not email the "Workflow Supervisor".
 */

/**
 * Implemenation of the a transition email proxy.
 */
class DkanWorkflowEmailProxy {

  /**
   * Constructor function.
   */
  public function __construct($message) {
    $this->roles = array_flip(user_roles());
    $this->node = $message['params']['node'];
    $this->user = user_load_by_mail($message['to']);
  }

  /**
   * Decision process for sending messages.
   */
  public function send() {
    return $this->isCreator() || $this->isGroupModerator() || $this->isUngroupedSupervisor();
  }

  /**
   * Allow emails to creator of the node.
   */
  private function isCreator() {
    return ($this->user->uid == $this->node->uid);
  }

  /**
   * Allow emails to group Moderators that share same group as node.
   */
  private function isGroupModerator() {
    if (user_has_role($this->roles['Workflow Moderator'], $this->user)) {
      $user_groups = og_get_entity_groups('user', $this->user);
      $node_groups = og_get_entity_groups('node', $this->node);
      if (isset($user_groups['node']) && isset($node_groups['node'])) {
        $intersect = array_intersect($user_groups['node'], $node_groups['node']);
      }
      else {
        $intersect = array();
      }
      return !empty($intersect);
    }
    return FALSE;
  }

  /**
   * Allow emails to Supervisors for groupless nodes.
   */
  private function isUngroupedSupervisor() {
    if (user_has_role($this->roles['Workflow Supervisor'], $this->user)) {
      if (empty(og_get_entity_groups('node', $this->node))) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
