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
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritDoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    return new DatabaseTable($this->connection, $identifier);
  }

}
