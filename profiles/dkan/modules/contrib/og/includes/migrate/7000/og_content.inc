<?php

/**
 * @file
 * Upgrade nodes that are group-content, by settings the correct field
 * value.
 */

class OgMigrateContent extends Migration {

  public function __construct() {
    $this->description = t('Upgrade nodes that are group-content, by settings the correct field value.');

    foreach (node_type_get_names() as $bundle => $value) {
      // Dependent on a dynamic migration.
      $machine_name = 'OgMigrateGroup' . ucfirst($bundle);
      if (MigrationBase::getInstance($machine_name, 'OgMigrateGroup', array('bundle' => $bundle))) {
        $this->dependencies[] = $machine_name;
      }
    }

    $query = db_select('d6_og_ancestry', 'oga');
    $query->innerJoin('node', 'n', 'n.nid = oga.nid');

    $query
      ->fields('oga')
      ->orderBy('n.nid');

    $this->query = $query;

    parent::__construct();

    $source_key = array(
      'nid' => array(
        'type' => 'int',
        'not null' => TRUE,
        'description' => 'Node ID of the group-content.',
        // Prevent nid to be ambiguous in MigrateSourceSQL::performRewind().
        'alias' => 'n',
      ),
      'group_nid' => array(
        'type' => 'int',
        'not null' => TRUE,
        'description' => 'Node ID of the group.',
      ),
    );

    $this->map = new MigrateSQLMap($this->machineName, $source_key, MigrateDestinationOGMembership::getKeySchema());
    $this->source = new MigrateSourceSQL($this->query);
    $this->destination = new MigrateDestinationOGMembership();

    $this->addFieldMapping('group_type', NULL)->defaultValue('node');
    $this->addFieldMapping('gid', 'group_nid');

    $this->addFieldMapping('entity_type', NULL)->defaultValue('node');
    $this->addFieldMapping('etid', 'nid');
  }
}
