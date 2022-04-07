<?php

namespace Drupal\common\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;

/**
 * DKAN JobStore Factory.
 */
class JobStoreFactory implements FactoryInterface {

  /**
   * JobStore instances keyed by unique identifiers.
   *
   * @var \Drupal\common\Storage\JobStore[]
   */
  private $instances = [];

  /**
   * Drupal database connection.
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
   * {@inheritdoc}
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($this->instances[$identifier])) {
      $this->instances[$identifier] = new JobStore($identifier, $this->connection);
    }

    return $this->instances[$identifier];
  }

}
