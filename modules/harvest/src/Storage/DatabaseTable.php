<?php

namespace Drupal\harvest\Storage;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\AbstractDatabaseTable;

/**
 * Harvest database table storage.
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
   */
  public function __construct(Connection $connection, string $identifier) {
    $this->identifier = $identifier;
    $this->setOurSchema();
    parent::__construct($connection);
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function retrieve(string $id) {
    $result = parent::retrieve($id);
    return ($result === NULL) ? NULL : $result->data;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  protected function getTableName() {
    return "{$this->identifier}";
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  protected function prepareData(string $data, string $id = NULL): array {
    return ["id" => $id, "data" => $data];
  }

  /**
   * Inherited.
   *
   * @inheritdoc
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
