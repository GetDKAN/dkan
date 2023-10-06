<?php

namespace Drupal\common\Util;

use Drupal\Core\Database\Connection;

/**
 * Utility class of methods for mitigating/updating legacy job store tables.
 */
class JobStoreUtil {

  /**
   * Class names we know how to fix as duplicates.
   *
   * @var array|string[]
   */
  public array $fixableClassNames = [
    'Drupal\datastore\Plugin\QueueWorker\ImportJob',
    'FileFetcher\FileFetcher',
  ];

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Get all the jobstore tables.
   *
   * @return array
   *   A list of all the tables that start with 'jobstore_'.
   */
  public function getAllJobstoreTables(): array {
    if ($jobstore_tables = $this->connection->schema()
      ->findTables('%jobstore%')
    ) {
      return $jobstore_tables;
    }
    return [];
  }

  /**
   * Get a list of jobstore tables that we don't know how to fix.
   *
   * @return array
   *   List of table names starting with 'jobstore_' for which we don't have a
   *   known update path.
   */
  public function getUnknownJobstoreTables(): array {
    $known = [];
    foreach ($this->fixableClassNames as $class_name) {
      $known[] = $this->getTableNameForClassname($class_name);
      $known[] = $this->getDeprecatedTableNameForClassname($class_name);
    }
    return array_diff($this->getAllJobstoreTables(), $known);
  }

  /**
   * A list of deprecated tables currently in use.
   *
   * Based on a list of known classes.
   *
   * @return string[]
   *   All the deprecated table names currently in use, keyed by their class
   *   name.
   */
  public function getAllDeprecatedJobstoreTableNames(): array {
    $deprecated_table_names = [];
    foreach ($this->fixableClassNames as $classname) {
      if ($this->tableIsDeprecatedNameForClassname($classname)) {
        $deprecated = $this->getDeprecatedTableNameForClassname($classname);
        $deprecated_table_names[$classname] = $deprecated;
      }
    }
    return $deprecated_table_names;
  }

  /**
   * Rename all deprecated tables to use new table names.
   *
   * @return array
   *   Array of renamed tables, where key is the old name and value is the new
   *   name.
   */
  public function renameDeprecatedJobstoreTables(): array {
    if ($deprecated_table_names = $this->getAllDeprecatedJobstoreTableNames()) {
      $renamed = [];
      foreach ($deprecated_table_names as $class_name => $deprecated_table_name) {
        $job_store = new JobStoreAccessor($class_name, $this->connection);
        $table_name = $job_store->accessTableName();
        $renamed[$deprecated_table_name] = $table_name;
        $this->connection->schema()->renameTable(
          $deprecated_table_name,
          $table_name
        );
      }
      return $renamed;
    }
    return [];
  }

  /**
   * Merge all the duplicate jobstore tables we know how to fix.
   *
   * @return array
   *   List of tables we merged. Deprecated table name is key, new table name is
   *   value.
   *
   * @see \Drupal\common\Util\JobStoreUtil::reconcileDuplicateJobstoreTable()
   */
  public function reconcileDuplicateJobstoreTables(): array {
    $results = [];
    $class_names = $this->getClassesForDuplicateJobstoreTables();
    foreach ($class_names as $class_name) {
      $results[$this->getDeprecatedTableNameForClassname($class_name)] =
        $this->getTableNameForClassname($class_name);
      $this->reconcileDuplicateJobstoreTable($class_name);
    }
    return $results;
  }

  /**
   * Merge duplicate jobstore tables for a given job class.
   *
   * 'Merge' means taking all the entries in the deprecated table and moving
   * them to the non-deprecated table, except for entries with common ref_uuid
   * values. Entries in the deprecated table with common ref_uuid values are
   * discarded, in favor of the ones in the non-deprecated table. Finally, the
   * deprecated table is dropped.
   *
   * @param string $class_name
   *   Class name identifier for the jobstore table to merge.
   */
  public function reconcileDuplicateJobstoreTable(string $class_name) {
    $job_store_accessor = new JobStoreAccessor($class_name, $this->connection);
    $deprecated_table_name = $job_store_accessor->accessDeprecatedTableName();
    $table_name = $job_store_accessor->accessTableName();

    // Hold all this in a transaction so that we don't lose anything.
    $transaction = $this->connection->startTransaction();

    // Are there overlapping ref_uuids?
    $query = $this->connection->select($deprecated_table_name, 'd')
      ->fields('d', ['ref_uuid']);
    $query->join($table_name, 'n', 'd.ref_uuid = n.ref_uuid');
    // @todo Is there a better way to get only the ref_uuid values?
    $overlap_uuids = array_keys($query->execute()
      ->fetchAllAssoc('ref_uuid'));

    // Select everything in the deprecated table, except the overlaps.
    $query = $this->connection->select($deprecated_table_name, 'd')
      ->fields('d', ['ref_uuid', 'job_data']);
    // Conditionalize on the existence of the overlapping UUIDs, because the
    // query will be an error otherwise.
    if (!empty($overlap_uuids)) {
      $query = $query->condition('d.ref_uuid', $overlap_uuids, 'NOT IN');
    }

    // Insert that select into the new table.
    $this->connection->insert($table_name)
      ->from($query)
      ->execute();

    // Remove the deprecated table.
    $this->connection->schema()->dropTable($deprecated_table_name);

    // Release the transaction.
    unset($transaction);
  }

  /**
   * Get a list of classes which have both deprecated and non-deprecated tables.
   *
   * @return string[]
   *   Array of class names.
   */
  public function getClassesForDuplicateJobstoreTables(): array {
    $duplicates = [];
    foreach ($this->fixableClassNames as $class_name) {
      if ($this->duplicateJobstoreTablesForClassname($class_name)) {
        $duplicates[$class_name] = $class_name;
      }
    }
    return $duplicates;
  }

  /**
   * Get a list of tables which have both deprecated and non-deprecated names.
   *
   * @return string[]
   *   Array of table names for values, with the deprecated table name as the
   *   key.
   */
  public function getDuplicateJobstoreTables(): array {
    $duplicates = [];
    foreach ($this->fixableClassNames as $class_name) {
      if ($this->duplicateJobstoreTablesForClassname($class_name)) {
        $duplicates[$this->getDeprecatedTableNameForClassname($class_name)] =
          $this->getTableNameForClassname($class_name);
      }
    }
    return $duplicates;
  }

  /**
   * Does the class map to more than one table?
   *
   * @param string $class_name
   *   Class name.
   *
   * @return bool
   *   TRUE if both deprecated and non-deprecated tables exist. FALSE otherwise.
   */
  public function duplicateJobstoreTablesForClassname(string $class_name): bool {
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
      ->tableExists($job_store->accessDeprecatedTableName()) &&
      !$this->connection->schema()
        ->tableExists($job_store->accessTableName());
  }

  /**
   * Get the deprecated table name for this class.
   *
   * @param string $className
   *   The class name.
   *
   * @return string
   *   The deprecated table name.
   */
  public function getDeprecatedTableNameForClassname(string $className): string {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $job_store->accessDeprecatedTableName();
  }

  /**
   * Get the non-deprecated table name for this class.
   *
   * @param string $className
   *   The class name.
   *
   * @return string
   *   The non-deprecated table name.
   */
  public function getTableNameForClassname(string $className): string {
    $job_store = new JobStoreAccessor($className, $this->connection);
    return $job_store->accessTableName();
  }

  /**
   * Given a keyed array, create a list array.
   *
   * @param array $keyed
   *   Key=>value array.
   *
   * @return array
   *   Input array, rearranged so that each item is [key, value].
   */
  public function keyedToList(array $keyed): array {
    $list = [];
    foreach ($keyed as $key => $value) {
      $list[] = [$key, $value];
    }
    return $list;
  }

  /**
   * Given a keyed array, create an array of decorated strings.
   *
   * @param array $keyed
   *   Key=>value array.
   * @param string $decorator
   *   String to insert between key and value.
   *
   * @return array
   *   Input array, rearranged so that each item is ['key decorator value'].
   */
  public function keyedToListDecorator(array $keyed, string $decorator): array {
    $decorated = [];
    foreach ($this->keyedToList($keyed) as $items) {
      $decorated[] = implode($decorator, $items);
    }
    return $decorated;
  }

}
