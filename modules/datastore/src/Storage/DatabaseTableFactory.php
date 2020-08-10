<?php

namespace Drupal\datastore\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;

/**
 * Class DatabaseTableFactory.
 */
class DatabaseTableFactory implements FactoryInterface {
  private $connection;

  private $databaseTables = [];

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
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

    if (!isset($this->databaseTables[$identifier])) {
      $this->databaseTables[$identifier] = $this->getDatabaseTable($resource);
    }

    return $this->databaseTables[$identifier];
  }

  /**
   * Protected.
   */
  protected function getDatabaseTable($resource) {
    return new DatabaseTable($this->connection, $resource);
  }

}
