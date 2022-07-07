<?php

namespace Drupal\datastore\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\indexer\IndexManager;

/**
 * DatabaseTable data object factory.
 */
class DatabaseTableFactory implements FactoryInterface {

  /**
   * Drupal database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Optional index manager service.
   *
   * @var null|\Drupal\indexer\IndexManager
   */
  private $indexManager;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
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
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($config['resource'])) {
      throw new \Exception("config['resource'] is required");
    }

    $resource = $config['resource'];
    $databaseTable = $this->getDatabaseTable($resource);
    if ($this->indexManager) {
      $databaseTable->setIndexManager($this->indexManager);
    }

    return $databaseTable;
  }

  /**
   * Protected.
   */
  protected function getDatabaseTable($resource) {
    return new DatabaseTable($this->connection, $resource);
  }

}
