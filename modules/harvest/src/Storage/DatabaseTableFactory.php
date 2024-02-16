<?php

namespace Drupal\harvest\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;

/**
 * Database table factory.
 */
class DatabaseTableFactory implements FactoryInterface {

  /**
   * Drupal database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Database table data objects.
   *
   * @var \Drupal\harvest\Storage\DatabaseTable
   */
  private $storage = [];

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
    if (!isset($this->storage[$identifier])) {
      $this->storage[$identifier] = $this->getDatabaseTable($identifier);
    }
    return $this->storage[$identifier];
  }

  /**
   * Protected.
   */
  protected function getDatabaseTable($identifier) {
    return new DatabaseTable($this->connection, $identifier);
  }

}
