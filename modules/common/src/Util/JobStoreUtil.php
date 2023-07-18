<?php

namespace Drupal\common\Util;

use Drupal\common\Storage\JobStore;
use Drupal\Core\Database\Connection;

/**
 * Utility class of methods for mitigating/updating legacy job store tables.
 */
class JobStoreUtil {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
    if ($jobstore_tables = $this->connection->schema()->findTables('%jobstore%')) {
      return $jobstore_tables;
    }
    return [];
  }

  public function getExistingJobstoreTablesForClassname(string $className): array {
    $tables = [];
    $job_store = new JobStoreAccessor($className, $this->connection);
    $potential_tables = [
      $job_store->accessTableName(),
      $job_store->accessDeprecatedTableName()
    ];
    foreach ($potential_tables as $potential_table) {
      if ($this->connection->schema()->tableExists($potential_table)) {
        $tables[$potential_table] = $potential_table;
      }
    }
    return $tables;
  }

}
