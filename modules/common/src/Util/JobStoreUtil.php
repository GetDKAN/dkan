<?php

namespace Drupal\common\Util;

use Drupal\common\Storage\JobStore;
use Drupal\Core\Database\Connection;

/**
 * Utility class of methods for mitigating/updating legacy job store tables.
 */
class JobStoreUtil {

  /**
   * Various class names which might have generated jobstore tables.
   *
   * @var string[]
   */
  public array $classNames = [
    'dkan\datastore\importer',
    'drupal\datastore\plugin\queueworker\filefetcherjob',
    'drupal\datastore\plugin\queueworker\importjob',
    'filefetcher\filefetcher',
  ];

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public static function getJobStoreSubclasses(): array {
    $subclasses = [];
    foreach (get_declared_classes() as $class) {
      if (is_subclass_of($class, JobStore::class)) {
        $subclasses[] = $class;
      }
    }
    return $subclasses;
  }

  public function getAllJobstoreTables(): array {
    if ($jobstore_tables = $this->connection->schema()
      ->findTables('%jobstore%')) {
      return $jobstore_tables;
    }
    return [];
  }

  public function getAllTableNamesForClassname(string $className): array {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return [
      $job_store->accessTableName(),
      $job_store->accessDeprecatedTableName(),
    ];
  }

  /**
   * Class names for which there are deprecated and non-deprecated tables.
   *
   * @return string[]
   *   Array of table names for values, with the deprecated table name as the
   *   key.
   */
  public function getDuplicateJobstoreTables(): array {
    $duplicates = [];
    foreach ($this->classNames as $class_name) {
      if ($this->duplicateJobstoreTablesForClass($class_name)) {
        $duplicates[$this->getDeprecatedTableNameForClassname($class_name)] = $this->getTableNameForClassname($class_name);
      }
    }
    return $duplicates;
  }

  public function duplicateJobstoreTablesForClass(string $class_name): bool {
    $table_name = $this->getTableNameForClassname($class_name);
    $deprecated_table_name = $this->getDeprecatedTableNameForClassname($class_name);
    return $this->connection->schema()->tableExists($table_name) &&
      $this->connection->schema()->tableExists($deprecated_table_name);
  }

  /**
   * Does this class name identifier use a deprecated table?
   *
   * NOTE: This will return FALSE if both tables exist.
   *
   * @param string $className
   *   Class name identifier to check.
   *
   * @return bool
   *   TRUE if ONLY the deprecated table exists, and NOT the non-deprecated one.
   *   FALSE otherwise.
   */
  public function tableIsDeprecatedNameForClassname(string $className): bool {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $this->connection->schema()
      // &&
      ->tableExists($job_store->accessDeprecatedTableName());
    // (!$this->connection->schema()
    //        ->tableExists($job_store->accessTableName()));
  }

  public function getDeprecatedTableNameForClassname(string $className): string {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $job_store->accessDeprecatedTableName();
  }

  public function getTableNameForClassname(string $className): string {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $job_store->accessTableName();
  }

  public function deprecatedTableExistsForClassname(string $className): bool {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $this->connection->schema()
      ->tableExists($job_store->accessDeprecatedTableName());
  }

  public function tableExistsForClassname(string $className): bool {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $this->connection->schema()
      ->tableExists($job_store->accessTableName());
  }

  public function getExistingJobstoreTablesForClassname(string $className): array {
    $tables = [];
    $potential_tables = $this->getAllTableNamesForClassname($className);
    foreach ($potential_tables as $potential_table) {
      if ($this->connection->schema()->tableExists($potential_table)) {
        $tables[$potential_table] = $potential_table;
      }
    }
    return $tables;
  }

}
