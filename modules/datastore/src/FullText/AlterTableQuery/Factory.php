<?php

namespace Drupal\datastore\FullText\AlterTableQuery;

use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\FullText\AlterTableQueryFactoryInterface;
use Drupal\datastore\FullText\AlterTableQueryInterface;

/**
 * Base alter table query factory.
 */
class Factory implements AlterTableQueryFactoryInterface {

  /**
   * Database connection factory.
   *
   * @var \Drupal\common\Storage\DatabaseConnectionFactoryInterface
   */
  protected DatabaseConnectionFactoryInterface $databaseConnectionFactory;

  /**
   * Alter query class name.
   *
   * @var string
   */
  protected string $queryClass;

  /**
   * Create an alter table query factory.
   */
  public function __construct(
    DatabaseConnectionFactoryInterface $database_connection_factory,
    string $query_class
  ) {
    $this->databaseConnectionFactory = $database_connection_factory;
    $this->queryClass = $query_class;
  }

  /**
   * {@inheritdoc}
   */
  public function setConnectionTimeout(int $timeout): self {
    $this->databaseConnectionFactory->setConnectionTimeout($timeout);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(string $datastore_table, array $indexes): AlterTableQueryInterface {
    return new $this->queryClass(
      $this->databaseConnectionFactory->getConnection(),
      $datastore_table,
      $indexes
    );
  }

}
