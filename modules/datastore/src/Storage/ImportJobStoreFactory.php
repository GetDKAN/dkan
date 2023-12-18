<?php

namespace Drupal\datastore\Storage;

use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;

class ImportJobStoreFactory extends JobStoreFactory {

  protected const TABLE_NAME = 'jobstore_2613055649_importjob';

  /**
   * {@inheritDoc}
   *
   * @param string $identifier
   *   (optional) An identifier. This is optional because unless there is an
   *   existing table with a deprecated name, we'll use the table name from
   *   self::TABLE_NAME.
   * @param array $config
   *   (optional) Ignored, because JobStore does not use it.
   *
   * @return \Drupal\common\Storage\DatabaseTableInterface
   *   Resulting JobStore object.
   */
  public function getInstance(string $identifier = '', array $config = []): DatabaseTableInterface {
    if ($identifier && $identifier !== self::TABLE_NAME) {
      // Silent error to be picked up by tests.
      @trigger_error(
        'Import job store identifier must be either empty or ' . self::TABLE_NAME . '.',
        E_USER_DEPRECATED
      );
    }
    $table_name = self::TABLE_NAME;
    $deprecated_table_name = $this->getDeprecatedTableName($identifier);
    // Figure out whether we need a separate deprecated table name. This will
    // be used in JobStore::destruct() to clean up deprecated tables if they
    // exist.
    if ($table_name === $deprecated_table_name) {
      $deprecated_table_name = '';
    }
    return new JobStore($table_name, $this->connection, $deprecated_table_name);
  }

}
