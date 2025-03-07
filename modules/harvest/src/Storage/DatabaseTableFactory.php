<?php

namespace Drupal\harvest\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * Database table data objects.
   *
   * @var \Drupal\harvest\Storage\DatabaseTable
   */
  private $storage = [];

  /**
   * Constructor.
   */
  public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher) {
    $this->connection = $connection;
    $this->eventDispatcher = $eventDispatcher;
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
    return new DatabaseTable($this->connection, $identifier, $this->eventDispatcher);
  }

}
