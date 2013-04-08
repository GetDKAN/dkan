<?php

/**
 * @file
 * Create user relation to group.
 */

class OgMigrateUser extends Migration {

  public function __construct() {
    parent::__construct();

    $this->description = t('Create user relation to group.');

    foreach (node_type_get_names() as $bundle => $value) {
      // Dependent on a dynamic migration.
      $machine_name = 'OgMigrateGroup' . ucfirst($bundle);
      if (MigrationBase::getInstance($machine_name, 'OgMigrateGroup', array('bundle' => $bundle))) {
        $this->dependencies[] = $machine_name;
      }
    }

    $this->query = db_select('d6_og_uid', 'ogu')
      ->fields('ogu');

    $source_key = array(
      'nid' => array(
        'type' => 'int',
        'not null' => TRUE,
        'description' => 'node ID of group.',
      ),
      'uid' => array(
        'type' => 'int',
        'not null' => TRUE,
        'description' => 'User ID of member.',
      ),
    );

    $this->map = new MigrateSQLMap($this->machineName, $source_key, MigrateDestinationOGMembership::getKeySchema());
    $this->source = new MigrateSourceSQL($this->query);
    $this->destination = new MigrateDestinationOGMembership();

    $field_names = array(
      'is_admin',
      'created',
    );
    foreach ($field_names as $field_name) {
      $this->addFieldMapping($field_name, $field_name);
    }

    $this->addFieldMapping('state', 'is_active');

    $this->addFieldMapping('group_type', NULL)->defaultValue('node');
    $this->addFieldMapping('gid', 'nid');

    $this->addFieldMapping('entity_type', NULL)->defaultValue('user');
    $this->addFieldMapping('etid', 'uid');
  }

  public function prepareRow($row) {
    $row->state = !empty($row->state) ? OG_STATE_ACTIVE : OG_STATE_PENDING;
    parent::prepareRow($row);
  }
}
