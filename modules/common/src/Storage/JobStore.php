<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;

/**
 * Retrieve a serialized job (datastore importer or harvest) from the database.
 */
class JobStore extends AbstractDatabaseTable {

  /**
   * The table name for this job store.
   */
  protected string $tableName;

  /**
   * A deprecated table name for this job store, if applicable.
   */
  protected string $deprecatedTableName;

  /**
   * Constructor.
   *
   * @param string $tableName
   *   Table name for this jobstore table.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param string $deprecatedTableName
   *   (Optional) Deprecated table name, if there is one.
   */
  public function __construct(string $tableName, Connection $connection, string $deprecatedTableName = '') {
    $this->tableName = $tableName;
    $this->deprecatedTableName = $deprecatedTableName;
    $this->setOurSchema();
    parent::__construct($connection);
  }

  /**
   * {@inheritDoc}
   *
   * Retrieve job data.
   */
  public function retrieve(string $id) {
    $result = parent::retrieve($id);
    if ($result && isset($result->job_data)) {
      return $result->job_data;
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  protected function getTableName() {
    return $this->tableName;
  }

  /**
   * Private.
   */
  private function setOurSchema() {
    $schema = [
      'fields' => [
        'ref_uuid' => [
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
        ],
        'job_data' => ['type' => 'text', 'length' => 65535],
      ],
      'indexes' => [
        'ref_uuid' => ['ref_uuid'],
      ],
      'foreign keys' => [
        'ref_uuid' => ['table' => 'node', 'columns' => ['uuid' => 'uuid']],
      ],
      'primary key' => ['ref_uuid'],
    ];

    $this->setSchema($schema);
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  protected function prepareData(string $data, ?string $id = NULL): array {
    return ['ref_uuid' => $id, 'job_data' => $data];
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function primaryKey() {
    return 'ref_uuid';
  }

  /**
   * Drop the table if it exists.
   *
   * Will also drop the deprecated table if it exists.
   */
  public function destruct() {
    parent::destruct();
    // If the factory gave us a deprecated table name, we should clean that up,
    // too.
    if ($this->deprecatedTableName ?? FALSE) {
      $this->connection->schema()->dropTable($this->deprecatedTableName);
    }
  }

}
