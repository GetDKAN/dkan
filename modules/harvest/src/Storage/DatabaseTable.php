<?php

namespace Drupal\harvest\Storage;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\AbstractDatabaseTable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Harvest database table storage.
 *
 * Currently handles these tables: harvest_[id]_runs, harvest_[id]_items,
 * harvest_[id]_hashes.
 *
 * @see \Drupal\harvest\Storage\DatabaseTableFactory::getDatabaseTable()
 * @see \Drupal\harvest\Entity\HarvestPlanRepository
 */
class DatabaseTable extends AbstractDatabaseTable {

  /**
   * Database table identifier.
   *
   * @var string
   */
  private $identifier;

  /**
   * DatabaseTable constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal's database connection object.
   * @param string $identifier
   *   Each unique identifier represents a table.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   */
  public function __construct(
    Connection $connection,
    string $identifier,
    EventDispatcherInterface $eventDispatcher
  ) {
    $this->identifier = $identifier;
    $this->setOurSchema();
    parent::__construct($connection, $eventDispatcher);
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function retrieve(string $id) {
    $result = parent::retrieve($id);
    return ($result === NULL) ? NULL : $result->data;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "{$this->identifier}";
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareData(string $data, ?string $id = NULL): array {
    return ["id" => $id, "data" => $data];
  }

  /**
   * {@inheritdoc}
   */
  public function primaryKey() {
    return "id";
  }

  /**
   * Private.
   */
  private function setOurSchema() {
    $schema = [
      'fields' => [
        'id' => ['type' => 'varchar', 'not null' => TRUE, 'length' => 190],
        'data' => ['type' => 'text', 'length' => 65535],
      ],
      'primary key' => ['id'],
    ];

    $this->setSchema($schema);
  }

}
