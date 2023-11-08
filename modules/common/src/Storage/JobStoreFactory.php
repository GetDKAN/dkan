<?php

namespace Drupal\common\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;

/**
 * DKAN JobStore Factory.
 */
class JobStoreFactory implements FactoryInterface {

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
    return new JobStore($identifier, $this->connection);
  }

}
