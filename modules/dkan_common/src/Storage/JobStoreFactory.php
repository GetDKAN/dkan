<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * DKAN JobStore Factory.
 *
 * @deprecated This class still exists in code to provide backwards
 *   compatibility.
 *
 * @todo Add FileFetcherJobStoreFactory as well.
 *
 * @see \Drupal\common\Storage\AbstractJobStoreFactory
 */
class JobStoreFactory implements StorageFactoryInterface {

  use DeprecatedJobStoreFactoryTrait;

  /**
   * Drupal database connection.
   */
  protected Connection $connection;

  /**
   * The event dispatcher.
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher) {
    $this->connection = $connection;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritdoc}
   *
   * Identifier is an abritrary string used to identify this particular
   * jobstore. Historically, the string has been a class name, but it can be
   * any string as long as it is suitable for use in a database table name.
   *
   * Here, JobStoreFactory will use the identifier to assemble a database table
   * with a name such as 'jobstore_[identifier]' or 'jobstore_[hash]_class',
   * or 'jobstore_deprecated_class_name'.
   *
   * The identifier should not contain \, unles it is a fully-qualified class
   * name.
   *
   * The configuration parameter is unused, for JobStore instances.
   */
  public function getInstance(string $identifier, array $config = []): DatabaseTableInterface {
    $table_name = $this->getTableName($identifier);
    $deprecated_table_name = $this->getDeprecatedTableName($identifier);
    // Figure out whether we need a separate deprecated table name. This will
    // be used in JobStore::destruct() to clean up deprecated tables if they
    // exist.
    if ($table_name === $deprecated_table_name) {
      $deprecated_table_name = '';
    }
    return new JobStore($table_name, $this->connection, $this->eventDispatcher, $deprecated_table_name);
  }

}
