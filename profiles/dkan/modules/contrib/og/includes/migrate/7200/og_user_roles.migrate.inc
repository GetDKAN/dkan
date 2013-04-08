<?php

/**
 * @file
 * Update the group ID in user roles.
 *
 * Class should be included only if this is an upgrade from branch 7.x-1.x
 * to branch 7.x-2.x
 */

class OgMigrateUserRoles extends OgEntityMigration {

  public $tableName = 'og_users_roles';

  protected $dependencies = array('OgMigrateRoles');

  protected $defaultRoles;

  protected $sourceKey = array(
    'uid' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'rid' => array(
      'type' => 'int',
      'not null' => TRUE,
    ),
    'gid' => array(
      'type' => 'int',
      'not null' => TRUE,
      // Set the alias, so the query in MigrateSourceSQL::performRewind()
      // will not fail.
      'alias' => 'og',
    ),
  );

  public function __construct() {
    $this->description = t('Update the group ID in user roles.');

    $query = db_select('temp_og_users_roles', 'ogur');
    $query->innerJoin('og', 'og', 'ogur.gid = og.gid');

    $query
      ->fields('ogur', array('uid', 'rid'));

    $query->addField('og', 'etid', 'gid');
    $query->addField('og', 'entity_type', 'group_type');

    $this->query = $query;

    parent::__construct();

    $this->addFieldMapping('uid', 'uid');
    $this->addFieldMapping('rid');
    $this->addFieldMapping('gid');
    $this->addFieldMapping('group_type');
  }

  /**
   * Override Migration::preImport().
   */
  protected function preImport() {
    parent::preImport();
    $this->defaultRoles = og_roles('', '');
  }

  public function prepare($entity, $row) {
    $group_type = $row->group_type;
    $gid = $row->gid;

    if (!$group = entity_load_single($group_type, $gid)) {
      // Some installations might have missing entities, so we don't assume
      // they exist.
      return;
    }
    // Get the bundle from the entity.
    list(,, $bundle) = entity_extract_ids($group_type, $group);

    // Get the per-bundle roles, and replace the role ID, with the
    // per-bundle role ID.
    $og_roles = og_roles($group_type, $bundle, $gid);
    if (!empty($this->defaultRoles[$row->rid]) && $rid = array_search($this->defaultRoles[$row->rid], $og_roles)) {
      // Assign the new role ID.
      $entity->rid = $rid;
    }

    $entity->group_type = $row->group_type;
    $entity->gid = $row->gid;
  }

  /**
   * Override Migration::postImport().
   *
   * Remove the global roles. We didn't do so it in OgMigrateRoles as
   * we need it in this class, when calling og_roles().
   * Also, delete legacy tables.
   */
  protected function postImport() {
    if (!$this->isComplete() || !$this->defaultRoles) {
      return;
    }
    parent::postImport();

    foreach (array_keys($this->defaultRoles) as $rid) {
      og_role_delete($rid);
    }
  }
}
