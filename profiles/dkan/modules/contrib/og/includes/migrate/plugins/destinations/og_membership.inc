<?php

/**
 * @file
 * Import Organic group memberships.
 */

/**
 * Destination class implementing migration into an OG Audience field.
 */
class MigrateDestinationOGMembership extends MigrateDestination {

  public function __toString() {
    return t('og-membership');
  }

  static public function getKeySchema() {
    return array(
      'id' => array(
        'description' => "The group membership's unique ID.",
        'type' => 'int',
        'not null' => TRUE,
      ),
    );
  }

  /**
   * Delete a membership.
   *
   * @param $id
   *  IDs to be deleted.
   */
  public function rollback(array $id) {
    $membership = og_membership_load($id);
    og_ungroup($membership->group_type, $membership->gid, $membership->entity_type, $membership->etid);
  }

  /**
   * Delete a batch of memberships at once.
   *
   * @param $ids
   *   Array of membership ids to be deleted.
   */
  public function bulkRollback(array $ids) {
    og_membership_delete_multiple($ids);
  }

  /**
   * Import a single membership.
   *
   * @param $entity
   *  Object object to build. Prefilled with any fields mapped in the Migration.
   * @param $row
   *  Raw source data object - passed through to prepare/complete handlers.
   * @return array
   *  Array of key fields of the object that was saved if
   *  successful. FALSE on failure.
   */
  public function import(stdClass $entity, stdClass $row) {
    if (empty($entity->group_type)) {
      throw new MigrateException('Missing group type.');
    }
    if (empty($entity->gid)) {
      throw new MigrateException('Missing group ID.');
    }
    if (empty($entity->entity_type)) {
      throw new MigrateException('Missing entity type.');
    }
    if (empty($entity->etid)) {
      throw new MigrateException('Missing entity ID.');
    }

     // Set state, make sure legacy states from OG6 are converted to
     // pending.
    if (isset($entity->state)) {
      $state = $entity->state ? OG_STATE_ACTIVE : OG_STATE_PENDING;
    }
    else {
      $state = OG_STATE_ACTIVE;
    }
    $this->prepare($entity, $row);
    // Allow passing OG-membership fields via og_group().
    $values = (array) $entity;
    $values['entity'] = $entity->etid;
    $values['state'] = $state;
    $values['created'] = isset($entity->created) ? $entity->created : REQUEST_TIME;

    $og_membership = og_group($entity->group_type, $entity->gid, $values);

    if ($entity->entity_type == 'user') {
      $cache = &drupal_static(__FUNCTION__, array());
      if (isset($cache["$entity->group_type:$entity->gid"])) {
        $og_roles = $cache["$entity->group_type:$entity->gid"];
      }
      else {
        $og_roles = og_roles($entity->group_type, NULL, $entity->gid);
        $cache["$entity->group_type:$entity->gid"] = $og_roles;
      }
      if (!empty($entity->is_admin)) {
        $admin_rid = array_search(OG_ADMINISTRATOR_ROLE, $og_roles);
        og_role_grant($entity->group_type, $entity->gid, $entity->etid, $admin_rid);
      }
      if (isset($entity->group_roles)) {
        foreach ($entity->group_roles as $role) {
          $rid = array_search($role, $og_roles);
          og_role_grant($entity->group_type, $entity->gid, $entity->etid, $rid);
        }
      }
    }

    $this->complete($entity, $row);

    return array($og_membership->id);
  }

  /**
   * Give handlers a shot at modifying the object before saving it.
   *
   * @param $entity
   *   OGMembership object to build. Prefilled with any fields mapped in
   *   the Migration.
   * @param $source_row
   *   Raw source data object - passed through to prepare handlers.
   */
  public function prepare($entity, stdClass $row) {
    // We do nothing here but allow child classes to act.
    $migration = Migration::currentMigration();
    $entity->migrate = array(
      'machineName' => $migration->getMachineName(),
    );

    // Call any general handlers.
    migrate_handler_invoke_all('og_membership', 'prepare', $entity, $row);
    // Then call any prepare handler for this specific Migration.
    if (method_exists($migration, 'prepare')) {
      $migration->prepare($entity, $row);
    }
  }

  /**
   * Give handlers a shot at modifying the object (or taking additional action)
   * after saving it.
   *
   * @param $entity
   *   OGMembership object to build. This is the complete object after
   *   saving.
   * @param $source_row
   *   Raw source data object - passed through to complete handlers.
   */
  public function complete($entity, stdClass $row) {
    // We do nothing here but allow child classes to act.
    $migration = Migration::currentMigration();
    $entity->migrate = array(
      'machineName' => $migration->getMachineName(),
    );

    // Call any general handlers.
    migrate_handler_invoke_all('og_membership', 'complete', $entity, $row);
    // Then call any complete handler for this specific Migration.
    if (method_exists($migration, 'complete')) {
      $migration->complete($entity, $row);
    }
  }

  public function fields() {
    return array(
      'group_type' => 'Group entity type',
      'gid' => 'Group entity id',
      'entity_type' => 'Entity type',
      'etid' => 'Entity ID (e.g. user ID)',
      'state' => 'User\'s group membership can be OG_STATE_ACTIVE or OG_STATE_PENDING. Defaults to OG_STATE_ACTIVE.',
      'created' => 'Create date for this membership. Defaults to REQUEST_TIME',
      'is_admin' => 'Legacy: Is member an administrator in this group. Defaults to No.',
      'group_roles' => 'Array of role names to assign to the user in this group.',
    );
  }
}
