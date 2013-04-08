<?php

/**
 * @file
 * Parent class to OG migration.
 */

abstract class OgEntityMigration extends Migration {

  public $keyName = 'id';

  public function __construct() {
    parent::__construct(MigrateGroup::getInstance('og_7200'));

    // Create a map object for tracking the relationships between source
    // rows.
    if (empty($this->sourceKey)) {
      $this->sourceKey = array(
        $this->keyName => array(
          'type' => 'int',
          'not null' => TRUE,
        ),
      );
    }

    $this->map = new MigrateSQLMap($this->machineName, $this->sourceKey, MigrateDestinationTable::getKeySchema($this->tableName));
    $this->source = new MigrateSourceSQL($this->query);
    $this->destination = new MigrateDestinationTable($this->tableName);
  }
}
