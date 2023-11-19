<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;

/**
 * Retrieve a serialized job (datastore importer or harvest) from the database.
 */
class JobStore extends AbstractDatabaseTable {

  /**
   * Store the name of the table so that we do not have to recompute.
   *
   * @var string
   */
  protected string $tableName;

  /**
   * Constructor.
   *
   * @param string $tableName
   *   Table name for this jobstore table.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(string $tableName, Connection $connection) {
    $this->tableName = $tableName;
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
  protected function prepareData(string $data, string $id = NULL): array {
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

}
