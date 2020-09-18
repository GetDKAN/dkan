<?php

namespace Drupal\common\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;

/**
 * Class JobStoreFactory.
 */
class JobStoreFactory implements FactoryInterface {
  private $instances = [];
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
