<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;

/**
 * DKAN JobStore factory base class.
 */
abstract class AbstractJobStoreFactory implements StorageFactoryInterface {

  use DeprecatedJobStoreFactoryTrait;

  /**
   * The import job store table name.
   *
   * Override this for your table name.
   *
   * @var string
   */
  protected string $tableName = '';

  /**
   * Drupal database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritDoc}
   *
   * @param string $identifier
   *   (optional) An identifier. This is optional because unless there is an
   *   existing table with a deprecated name, we'll use the table name from
   *   $this->TABLE_NAME.
   * @param array $config
   *   (optional) Ignored, because JobStore does not use it.
   *
   * @return \Drupal\common\Storage\DatabaseTableInterface
   *   Resulting JobStore object.
   */
  public function getInstance(string $identifier = '', array $config = []): DatabaseTableInterface {
    $table_name = $this->tableName;
    // For historical reasons, we keep the getInstance() method signature, but
    // we also want to enforce our static table name.
    if ($identifier && $identifier !== $this->tableName) {
      // If the caller passed an unusual identifier, we should try to use the
      // table name they desire for backwards compatibility.
      $table_name = $this->getTableName($identifier);
    }
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
