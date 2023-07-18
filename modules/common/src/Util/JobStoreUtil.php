<?php

namespace Drupal\common\Util;

use Drupal\common\Storage\JobStore;
use Drupal\Core\Database\Connection;

class JobStoreUtil {

  public static function getJobStoreSubclasses(): array {
    $subclasses = [];
    foreach (get_declared_classes() as $class) {
      if (is_subclass_of($class, JobStore::class)) {
        $subclasses[] = $class;
      }
    }
    return $subclasses;
  }

  public static function getAllJobstoreTables(Connection $connection): array {
    $jobstore_tables = [];
    if ($tables = $connection->schema()->findTables('%jobstore%')) {
      $jobstore_tables = array_values($tables);
    }
    return $jobstore_tables;
  }

}
