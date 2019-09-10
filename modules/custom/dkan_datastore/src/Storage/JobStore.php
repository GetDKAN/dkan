<?php

namespace Drupal\dkan_datastore\Storage;

use Procrastinator\Job\Job;
use Drupal\Core\Database\Connection;

/**
 * Retrieve a serialized job (datastore importer or harvest) from the database.
 *
 * @todo should probably be a service in its own module.
 */
class JobStore {

  /**
   * The database connection to use for querrying jobstore tables.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Get.
   */
  public function retrieve(string $uuid, string $jobClass) {
    $tableName = $this->getTableName($jobClass);

    $this->validateJobClassAndTableExistence($jobClass, $tableName);

    $result = $this->connection->select($tableName, 't')
      ->fields('t', ['job_data'])
      ->condition('ref_uuid', $uuid)
      ->execute()
      ->fetch();
    if (!empty($result)) {
      $job = $jobClass::hydrate($result->job_data);
    }
    if (isset($job) && ($job instanceof $jobClass)) {
      return $job;
    }
  }

  /**
   * Retrieve all.
   */
  public function retrieveAll(string $jobClass): array {
    $tableName = $this->getTableName($jobClass);

    $this->validateJobClassAndTableExistence($jobClass, $tableName);

    $result = $this->connection->select($tableName, 't')
      ->fields('t', ['ref_uuid', 'job_data'])
      ->execute()
      ->fetchAll();

    if ($result === FALSE) {
      throw new \Exception("No data in table: $tableName");
    }

    return array_reduce($result, function ($carry, $item) use ($jobClass) {
      $job = $jobClass::hydrate($item->job_data);
      if (isset($job) && ($job instanceof $jobClass)) {
        $carry[$item->ref_uuid] = $job;
      }
      return $carry;
    }, []);
  }

  /**
   * Private.
   */
  private function validateJobClassAndTableExistence($jobClass, $tableName) {
    if (!$this->validateJobClass($jobClass)) {
      throw new \Exception("Invalid jobType provided: $jobClass");
    }

    if (!$this->tableExists($tableName)) {
      $this->createTable($tableName);
    }
  }

  /**
   * Store.
   */
  public function store(string $uuid, Job $job) {
    $jobClass = get_class($job);
    $tableName = $this->getTableName($jobClass);

    if (!$this->tableExists($tableName)) {
      $this->createTable($tableName);
    }

    $existing_id = $this->connection->select($tableName, 't')
      ->fields('t', ['jid'])
      ->condition('ref_uuid', $uuid)
      ->execute()
      ->fetch();

    $data = json_encode($job);
    $values = ['ref_uuid' => $uuid, 'job_data' => $data];
    if (!$existing_id) {
      $q = $this->connection->insert($tableName);
      $q->fields(array_keys($values))
        ->values(array_values($values))
        ->execute();
    }
    else {
      $q = $this->connection->update($tableName);
      $q->fields($values)
        ->condition('jid', $existing_id->jid)
        ->execute();
    }
  }

  /**
   * Remove.
   */
  public function remove($uuid, $jobClass) {
    $tableName = $this->getTableName($jobClass);
    $this->connection->delete($tableName)
      ->condition('ref_uuid', $uuid)
      ->execute();
  }

  /**
   * Private.
   */
  private function getTableName($jobClass) {
    $safeClassName = strtolower(preg_replace('/\\\\/', '_', $jobClass));
    return 'jobstore_' . $safeClassName;
  }

  /**
   * Private.
   */
  private function createTable(string $tableName) {
    $schema = [
      'fields' => [
        'jid' => ['type' => 'serial', 'unsigned' => TRUE, 'not null' => TRUE],
        'ref_uuid' => ['type' => 'varchar', 'length' => 128],
        'job_data' => ['type' => 'text', 'length' => 65535],
      ],
      'indexes' => [
        'jid' => ['jid'],
      ],
      'foriegn_keys' => [
        'ref_uuid' => ['table' => 'node', 'columns' => ['uuid' => 'uuid']],
      ],
      'primary_key' => ['jid'],
    ];
    $this->connection->schema()->createTable($tableName, $schema);
  }

  /**
   * Check for existence of a table name.
   */
  private function tableExists($tableName) {
    $exists = $this->connection->schema()->tableExists($tableName);
    return $exists;
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

}
