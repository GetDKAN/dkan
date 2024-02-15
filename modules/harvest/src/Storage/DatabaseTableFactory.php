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
    // Tell the caller not to use the old style hashes table.
    if (str_ends_with($identifier, '_hashes')) {
      throw new \RuntimeException('The harvest hashes factory has moved. @see ' . HarvestHashesDatabaseTableFactory::class);
    }
    return new DatabaseTable($this->connection, $identifier);
  }

}
