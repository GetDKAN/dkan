<?php

namespace Drupal\datastore\Storage;

use Contracts\FactoryInterface;

use Drupal\datastore\Storage\DatabaseConnectionFactory;
use Drupal\indexer\IndexManager;

/**
 * Class DatabaseTableFactory.
 */
class DatabaseTableFactory implements FactoryInterface {

  /**
   * Database connection factory service.
   *
   * @var \Drupal\datastore\Storage\DatabaseConnectionFactory
   */
  protected $databaseConnectionFactory;

  /**
   * Optional index manager service.
   *
   * @var \Drupal\indexer\IndexManager|null
   */
  protected $indexManager;

  /**
   * Database table instances.
   *
   * @var \Drupal\datastore\Storage\DatabaseTable[]
   */
  protected $databaseTables = [];

  /**
   * Create DatabaseTableFactory instance.
   *
   * @param \Drupal\datastore\Storage\DatabaseConnectionFactory
   *   Database connection factory service instance.
   */
  public function __construct(DatabaseConnectionFactory $databaseConnectionFactory) {
    $this->databaseConnectionFactory = $databaseConnectionFactory;
  }

  /**
   * Set an optional index manager service.
   *
   * @param \Drupal\indexer\IndexManager $indexManager
   *   Index manager.
   */
  public function setIndexManager(IndexManager $indexManager) {
    $this->indexManager = $indexManager;
  }

  /**
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];

    if (!isset($this->databaseTables[$identifier])) {
      $this->databaseTables[$identifier] = $this->getDatabaseTable($resource);
      if ($this->indexManager) {
        $this->databaseTables[$identifier]->setIndexManager($this->indexManager);
      }
    }

    return $this->databaseTables[$identifier];
  }

  /**
   * Generate database table for the given resource.
   *
   * @param \Dkan\Datastore\Resource $resource
   *   The datastore resource being imported.
   *
   * @return \Drupal\datastore\Storage\DatabaseTable
   *   Database table instance.
   */
  protected function getDatabaseTable($resource): DatabaseTable {
    return new DatabaseTable($this->databaseConnectionFactory->getConnection(), $resource);
  }

}
