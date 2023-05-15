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
  private $jobClass;

  /**
   * Store the name of the table so we do not have to recompute.
   *
   * @var string
   */
  private $tableName;

  /**
   * Constructor.
   */
  public function __construct(string $jobClass, Connection $connection) {
    if (!$this->validateJobClass($jobClass)) {
      throw new \Exception("Invalid jobType provided: $jobClass");
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
   * Protected.
   */
  protected function getTableName() {
    if (empty($this->tableName)) {
      // Avoid table-name-too-long errors by hashing the FQN of the class.
      $exploded_class = explode("\\", $this->jobClass);
      $this->tableName = strtolower(implode('_', [
        'jobstore',
        crc32($this->jobClass),
        array_pop($exploded_class),
      ]));
    }
    return $this->tableName;
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

}
