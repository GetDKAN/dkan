<?php

namespace Drupal\common\Storage;

use Procrastinator\Job\Job;
use Drupal\Core\Database\Connection;

/**
 * Retrieve a serialized job (datastore importer or harvest) from the database.
 */
class JobStore extends AbstractDatabaseTable {

  /**
   * Procrastinator job class.
   *
   * @var string
   */
  private string $jobClass;

  /**
   * Store the name of the table so that we do not have to recompute.
   *
   * @var string
   */
  private string $tableName;

  /**
   * Constructor.
   *
   * @param string $jobClass
   *   Class name of the job object which is using this storage table. Must be
   *   a subclass of \Procrastinator\Job\Job.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   *
   * @throws \Exception
   *   Thrown if the job class is not valid.
   */
  public function __construct(string $jobClass, Connection $connection) {
    if (!$this->validateJobClass($jobClass)) {
      throw new \Exception('Invalid jobType provided: ' . $jobClass);
    }
    $this->jobClass = $jobClass;
    $this->setOurSchema();
    parent::__construct($connection);
  }

  /**
   * Get.
   */
  public function retrieve(string $id) {
    $result = parent::retrieve($id);
    if ($result && isset($result->job_data)) {
      return $result->job_data;
    }
    return NULL;
  }

  /**
   * Get the table name, preferring the deprecated one if it exists.
   *
   * Since we have two table names (one deprecated), we should try to find out
   * if the deprecated one exists. If it does, we use its name. Otherwise, we
   * use the new table name.
   *
   * @todo Phase out the use of the deprecated table name.
   */
  protected function getTableName() {
    if (empty($this->tableName)) {
      if ($this->tableExist($table = $this->getDeprecatedTableName())) {
        $this->tableName = $table;
      }
      else {
        // Avoid table-name-too-long errors by hashing the FQN of the class.
        $exploded_class = explode('\\', $this->jobClass);
        $this->tableName = strtolower(implode('_', [
          'jobstore',
          crc32($this->jobClass),
          array_pop($exploded_class),
        ]));
      }
    }
    return $this->tableName;
  }

  protected function getDeprecatedTableName(): string {
    $safeClassName = strtolower(preg_replace(
      '/\\\\/', '_',
      $this->jobClass
    ));
    return 'jobstore_' . $safeClassName;
  }

  /**
   * Private.
   */
  private function setOurSchema() {
    $schema = [
      'fields' => [
        'ref_uuid' => ['type' => 'varchar', 'length' => 128, 'not null' => TRUE],
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
   * Private.
   */
  private function validateJobClass(string $jobClass): bool {
    if (is_subclass_of($jobClass, Job::class)) {
      return TRUE;
    }
    return FALSE;
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

  /**
   * Drop the table if it exists.
   *
   * Will also drop the deprecated table if it exists.
   */
  public function destruct() {
    parent::destruct();
    $this->connection->schema()->dropTable($this->getDeprecatedTableName());
  }

}
