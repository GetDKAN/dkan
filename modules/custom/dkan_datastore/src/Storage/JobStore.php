<?php

namespace Drupal\dkan_datastore\Storage;

use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use Procrastinator\Job\Job;
use Drupal\Core\Database\Connection;

/**
 * Retrieve a serialized job (datastore importer or harvest) from the database.
 *
 * @todo should probably be a service in its own module.
 */
class JobStore implements StorerInterface, RetrieverInterface {

  /**
   * The database connection to use for querrying jobstore tables.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  private $jobClass;

  /**
   * Constructor.
   */
  public function __construct(string $jobClass, Connection $connection) {
    $this->jobClass = $jobClass;
    $this->connection = $connection;
  }

  /**
   * Get.
   */
  public function retrieve(string $identifier) {
    $tableName = $this->getTableName($this->jobClass);

    $this->validateJobClassAndTableExistence($this->jobClass, $tableName);

    $result = $this->connection->select($tableName, 't')
      ->fields('t', ['job_data'])
      ->condition('ref_uuid', $identifier)
      ->execute()
      ->fetch();

    return $result->job_data;
  }

  /**
   * Store.
   */
  public function store($data, string $id = NULL): string {
    $tableName = $this->getTableName($this->jobClass);

    if (!$this->tableExists($tableName)) {
      $this->createTable($tableName);
    }

    $existing_id = $this->connection->select($tableName, 't')
      ->fields('t', ['jid'])
      ->condition('ref_uuid', $id)
      ->execute()
      ->fetch();

    $values = ['ref_uuid' => $id, 'job_data' => $data];
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

    return $id;
  }

  /**
   * Retrieve all.
   */
  public function retrieveAll(): array {
    $tableName = $this->getTableName($this->jobClass);

    $this->validateJobClassAndTableExistence($this->jobClass, $tableName);

    $result = $this->connection->select($tableName, 't')
      ->fields('t', ['ref_uuid'])
      ->execute()
      ->fetchAll();

    if ($result === FALSE) {
      throw new \Exception("No data in table: $tableName");
    }

    return array_map(function ($item) {
      return $item->ref_uuid;
    }, $result);
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
   * Remove.
   */
  public function remove($uuid) {
    $tableName = $this->getTableName($this->jobClass);
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
