<?php

namespace Drupal\harvest\Storage;

use Drupal\Core\Database\Connection;
use Drupal\dkan\Storage\AbstractDatabaseTable;

/**
 * DatabaseTable.
 */
class DatabaseTable extends AbstractDatabaseTable {
  private $identifier;

  /**
   * DatabaseTable constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal's database connection object.
   * @param string $identifier
   *   Each unique identifier represents a table.
   */
  public function __construct(Connection $connection, string $identifier) {
    $this->identifier = $identifier;
    $this->setOurSchema();
    parent::__construct($connection);
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  public function retrieve(string $id) {
    $result = parent::retrieve($id);
    return $result->data;
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  protected function getTableName() {
    return "harvest_{$this->identifier}";
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  protected function prepareData(string $data, string $id = NULL): array {
    return ["id" => $id, "data" => $data];
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  protected function primaryKey() {
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
