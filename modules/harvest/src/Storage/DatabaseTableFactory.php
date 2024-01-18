<?php

namespace Drupal\harvest\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(
    Connection $connection,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
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
    if ($identifier === 'harvest_plans') {
      return new HarvestPlanEntityDatabaseTable($this->entityTypeManager);
    }
    return new DatabaseTable($this->connection, $identifier);
  }

}
