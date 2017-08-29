<?php

/**
 * @file
 * HarvestMigrateSQLMap file.
 */

/**
 * Base MigrateItem class for Harvest Migrations.
 *
 * Should be a simpler files retriving impletation for locally stored files.
 */
class HarvestMigrateSQLMap extends MigrateSQLMap {

  /**
   * Codes reflecting the orphaned status of a map row.
   */
  const STATUS_IGNORED_NO_SOURCE = 20;
  const SOURCEID1_EMPTY = 'N/A';

  /**
   * Names of tables created for tracking the migration.
   *
   * @var string
   */
  protected $logTable;

  /**
   * Get the log table name.
   */
  public function getLogTable() {
    return $this->logTable;
  }

  /**
   * Gets a qualified log table.
   *
   * Qualifying the log table name with the database name makes cross-db joins
   * possible. Note that, because prefixes are applied after we do this (i.e.,
   * it will prefix the string we return), we do not qualify the table if it has
   * a prefix. This will work fine when the source data is in the default
   * (prefixed) database (in particular, for simpletest), but not if the primary
   * query is in an external database.
   *
   * @return string
   *   Returns the log table name.
   *
   * @see self::getQualifiedMapTable()
   */
  public function getQualifiedLogTable() {
    $options = $this->connection->getConnectionOptions();
    $prefix = $this->connection->tablePrefix($this->logTable);
    if ($prefix) {
      return $this->logTable;
    }
    else {
      return $options['database'] . '.' . $this->logTable;
    }
  }

  /**
   * {@inheritdoc}
   *
   * Rewrite the parent constructor and add our specific bits that we couldn't
   * add as an override.
   */
  public function __construct($machine_name,
  array $source_key,
    array $destination_key,
  $connection_key = 'default',
  $options = array()) {

    // Save the logTable name before creating the tables.
    $db_connection = Database::getConnection('default', $connection_key);

    // Default generated table names, limited to 63 characters.
    $prefix_length = strlen($db_connection->tablePrefix());
    $this->logTable = 'migrate_log_' . drupal_strtolower($machine_name);
    $this->logTable = drupal_substr($this->logTable, 0, 63 - $prefix_length);

    parent::__construct($machine_name, $source_key, $destination_key, $connection_key, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureTables() {
    if (!$this->ensured) {
      if (!$this->connection->schema()->tableExists($this->mapTable)) {
        // Generate appropriate schema info for the map and message tables,
        // and map from the source field names to the map/msg field names.
        $count = 1;
        $source_key_schema = array();
        $pks = array();
        foreach ($this->sourceKey as $field_schema) {
          $mapkey = 'sourceid' . $count++;
          $source_key_schema[$mapkey] = $field_schema;
          $pks[] = $mapkey;
        }

        $fields = $source_key_schema;

        // Add destination keys to map table
        // TODO: How do we discover the destination schema?
        $count = 1;
        foreach ($this->destinationKey as $field_schema) {
          // Allow dest key fields to be NULL (for IGNORED/FAILED cases).
          $field_schema['not null'] = FALSE;
          $mapkey = 'destid' . $count++;
          $fields[$mapkey] = $field_schema;
        }
        $fields['needs_update'] = array(
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => MigrateMap::STATUS_IMPORTED,
          'description' => 'Indicates current status of the source row',
        );
        $fields['rollback_action'] = array(
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => MigrateMap::ROLLBACK_DELETE,
          'description' => 'Flag indicating what to do for this item on rollback',
        );
        $fields['last_imported'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'UNIX timestamp of the last time this row was imported',
        );
        $fields['hash'] = array(
          'type' => 'varchar',
          'length' => '32',
          'not null' => FALSE,
          'description' => 'Hash of source row data, for detecting changes',
        );
        $schema = array(
          'description' => t('Mappings from source key to destination key'),
          'fields' => $fields,
          'primary key' => $pks,
        );
        $this->connection->schema()->createTable($this->mapTable, $schema);

        // Now for the message table.
        $fields = array();
        $fields['msgid'] = array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        );
        $fields += $source_key_schema;

        $fields['mlid'] = array(
          'type' => 'int',
          'size' => 'big',
          'unsigned' => TRUE,
          'description' => 'For key for migrate_log table',
        );
        $fields['level'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 1,
        );
        $fields['message'] = array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
        );
        $schema = array(
          'description' => t('Messages generated during a migration process'),
          'fields' => $fields,
          'primary key' => array('msgid'),
          'indexes' => array('sourcekey' => $pks),
        );
        $this->connection->schema()->createTable($this->messageTable, $schema);

        // Generate appropriate schema info for the log table, and
        // map from the migrationid  field name to the log field name.
        $fields = array();

        $fields['mlid'] = array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'Primary key for migrate_log table',
        );
        $fields['muuid'] = array(
          'type' => 'varchar',
          'length' => '36',
          'not null' => FALSE,
          'description' => 'An UUID that identifies a full migration process (all chunks have the same muuid)',
        );
        $fields['created'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Number of created items',
        );
        $fields['updated'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Number of updated items',
        );
        $fields['unchanged'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Number of unchanged items',
        );
        $fields['failed'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Number of items that failed to import',
        );
        $fields['orphaned'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Number of previously imported items that are not provided by the source anymore',
        );

        $schema = array(
          'description' => t('Mappings from source key to destination key'),
          'fields' => $fields,
          // For documentation purposes only; foreign keys are not
          // created in the database.
          'foreign keys' => array(
            'migrate_log' => array(
              'table' => 'migrate_log',
              'columns' => array('mlid' => 'mlid'),
            ),
          ),
          'primary key' => array('mlid'),
        );

        $this->connection->schema()->createTable($this->logTable, $schema);
      }
      else {
        // Add any missing columns to the map table.
        if (!$this->connection->schema()->fieldExists($this->mapTable,
                                                      'rollback_action')) {
          $this->connection->schema()->addField($this->mapTable,
                                                'rollback_action', array(
                                                  'type' => 'int',
                                                  'size' => 'tiny',
                                                  'unsigned' => TRUE,
                                                  'not null' => TRUE,
                                                  'default' => 0,
                                                  'description' => 'Flag indicating what to do for this item on rollback',
                                                ));
        }
        if (!$this->connection->schema()->fieldExists($this->mapTable, 'hash')) {
          $this->connection->schema()->addField($this->mapTable, 'hash', array(
            'type' => 'varchar',
            'length' => '32',
            'not null' => FALSE,
            'description' => 'Hash of source row data, for detecting changes',
          ));
        }

        // Add any missing columns to the log table.
        if (!$this->connection->schema()->fieldExists($this->logTable, 'muuid')) {
          $this->connection->schema()->addField($this->logTable, 'muuid', array(
            'type' => 'varchar',
            'length' => '36',
            'not null' => FALSE,
            'description' => 'An UUID that identifies a full migration process (all chunks have the same muuid)',
          ));
        }
      }
      $this->ensured = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   *
   * Remove the associated log tables.
   */
  public function destroy() {
    parent::destroy();
    $this->connection->schema()->dropTable($this->logTable);
  }

  /**
   * Get the number of source records.
   *
   * Get number of source records previously imported
   * but not available from the source anymore.
   *
   * @return int
   *   Number of records errored out.
   */
  public function orphanedCount() {
    $query = $this->connection->select($this->mapTable);
    $query->addExpression('COUNT(*)', 'count');
    $query->condition('needs_update', self::STATUS_IGNORED_NO_SOURCE);
    $count = $query->execute()->fetchField();
    return $count;
  }

  /**
   * More generic method to query the map table.
   */
  public function lookupMapTable($needs_update_value = HarvestMigrateSQLMap::STATUS_IMPORTED, $sourceid1_values = array(), $sourceid1_condition = "IN", $destid1_values = array(), $destid1_condition = "IN") {
    migrate_instrument_start('HarvestMigrateSQLMap->lookupMapTable');
    $query = $this->connection->select($this->mapTable, 'map');
    $query->fields('map');

    if ($needs_update_value !== FALSE) {
      $query->condition("needs_update", $needs_update_value);
    }

    if (is_array($sourceid1_values) && !empty($sourceid1_values) && in_array($sourceid1_condition, array("IN", "NOT IN"))) {
      $query->condition('sourceid1', $sourceid1_values, $sourceid1_condition);
    }

    if (is_array($destid1_values) && !empty($sourceid1_values) && in_array($sourceid1_condition, array("IN", "NOT IN"))) {
      $query->condition('destid1', $destid1_values, $destid1_condition);
    }

    $result = $query->execute();
    $return = $result->fetchAllAssoc('sourceid1');
    migrate_instrument_stop('HarvestMigrateSQLMap->lookupMapTable');
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function saveMessage($source_key, $message, $level = Migration::MESSAGE_ERROR, $mlid = FALSE) {
    // Get the mlid.
    if (!$mlid) {
      // Default option. This is not the best way to get the mlid and will fail
      // miserably in concurrent migrations (iprobably not supported by
      // migration).
      $migration = Migration::currentMigration();
      if ($migration) {
        $fields['mlid'] = $migration->getLogId();
      }
    }
    else {
      $fields['mlid'] = $mlid;
    }

    // Only support single sourceid, we don't currently need the multip
    // sourceid. Alse allow null sourceid for global messages like the "source
    // is empty" message.
    if (isset($source_key)) {
      $fields['sourceid1'] = $source_key;
    }
    else {
      $fields['sourceid1'] = self::SOURCEID1_EMPTY;
    }
    $fields['level'] = $level;
    $fields['message'] = $message;
    $this->connection->insert($this->messageTable)
      ->fields($fields)
      ->execute();
  }

  /**
   * Rip off the MigrateSQLMap::deleteBulk().
   *
   * Only supports one key and deletes the map
   * table entry by sourceid.
   */
  public function deleteBulkFromMap(array $source_keys) {
    // If we have a single-column key, we can shortcut it.
    if (count($this->sourceKey) == 1) {
      $sourceids = array();
      foreach ($source_keys as $source_key) {
        $sourceids[] = $source_key;
      }
      $this->connection->delete($this->mapTable)
        ->condition('sourceid1', $sourceids, 'IN')
        ->execute();
    }
  }

}
