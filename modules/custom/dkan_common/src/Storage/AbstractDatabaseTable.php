<?php

namespace Drupal\dkan_common\Storage;

use Dkan\Datastore\Storage\StorageInterface;
use Dkan\Datastore\Storage\Database\SqlStorageTrait;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Select;
use Drupal\dkan_datastore\Storage\Query;

/**
 * AbstractDatabaseTable class.
 */
abstract class AbstractDatabaseTable implements StorageInterface {
  use SqlStorageTrait;

  protected $connection;

  /**
   * Get the full name of datastore db table.
   *
   * @return string
   *   Table name.
   */
  abstract protected function getTableName();

  /**
   * Prepare data.
   *
   * Transform the string data given into what should be use by the insert
   * query.
   */
  abstract protected function prepareData(string $data): array;

  /**
   * Get the primary key used in the table.
   */
  abstract protected function primaryKey();

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;

    if ($this->tableExist($this->getTableName())) {
      $this->setSchemaFromTable();
    }
  }

  /**
   * Inherited.
   *
   * @inheritDoc
   */
  public function retrieveAll(): array {
    $this->setTable();
    $tableName = $this->getTableName();

    $result = $this->connection->select($tableName, 't')
      ->fields('t', [$this->primaryKey()])
      ->execute()
      ->fetchAll();

    if ($result === FALSE) {
      return [];
    }

    return array_map(function ($item) {
      return $item->{$this->primaryKey()};
    }, $result);
  }

  /**
   * Store data.
   */
  public function store($data, string $id = NULL): string {
    $this->setTable();
    $data = $this->prepareData($data);

    $q = $this->connection->insert($this->getTableName());
    $q->fields(array_keys($this->schema['fields']));
    $q->values($data);
    $id = $q->execute();

    return "{$id}";
  }

  /**
   * Count rows in table.
   */
  public function count(): int {
    $this->setTable();
    $query = $this->connection->select($this->getTableName());
    return $query->countQuery()->execute()->fetchField();
  }

  /**
   * Run a query on the database table.
   *
   * @param \Drupal\dkan_datastore\Storage\Query $query
   *   Query obejct.
   */
  public function query(Query $query): array {
    $this->setTable();
    $db_query = $this->connection->select($this->getTableName(), 't')
      ->fields('t', $query->properties);

    $this->setQueryConditions($db_query, $query);
    $this->setQueryOrderBy($db_query, $query);
    $this->setQueryLimitAndOffset($db_query, $query);

    if ($query->count) {
      $db_query = $db_query->countQuery();
    }

    $result = $db_query->execute()->fetchAll();

    return $result;
  }

  /**
   * Private.
   */
  private function setTable() {
    if (!$this->tableExist($this->getTableName())) {
      if ($this->schema) {
        $this->tableCreate($this->getTableName(), $this->schema);
      }
      else {
        throw new \Exception("Could not instantiate the table due to a lack of schema.");
      }
    }
  }

  /**
   * Private.
   */
  private function setQueryConditions(Select $db_query, Query $query) {
    foreach ($query->conditions as $property => $value) {
      $db_query->condition($property, $value, "LIKE");
    }
  }

  /**
   * Private.
   */
  private function setQueryOrderBy(Select $db_query, Query $query) {
    foreach ($query->sort['ASC'] as $property) {
      $db_query->orderBy($property);
    }

    foreach ($query->sort['DESC'] as $property) {
      $db_query->orderBy($property, 'DESC');
    }
  }

  /**
   * Private.
   */
  private function setQueryLimitAndOffset(Select $db_query, Query $query) {
    if ($query->limit) {
      if ($query->offset) {
        $db_query->range($query->offset, $query->limit);
      }
      else {
        $db_query->range(0, $query->limit);
      }
    }
    elseif ($query->offset) {
      $db_query->range($query->limit);
    }
  }

  /**
   * Destroy.
   *
   * Drop the database table.
   */
  public function destroy() {
    if ($this->tableExist($this->getTableName())) {
      $this->connection->schema()->dropTable($this->getTableName());
    }
  }

  /**
   * Check for existence of a table name.
   */
  private function tableExist($table_name) {
    $exists = $this->connection->schema()->tableExists($table_name);
    return $exists;
  }

  /**
   * Create a table given a name and schema.
   */
  private function tableCreate($table_name, $schema) {
    $this->connection->schema()->createTable($table_name, $schema);
  }

  /**
   * Set the schema using the existing database table.
   */
  private function setSchemaFromTable() {
    $fields_info = $this->connection->query("DESCRIBE `{$this->getTableName()}`")->fetchAll();
    if (!empty($fields_info)) {
      $fields = $this->getFieldsFromFieldsInfo($fields_info);
      $this->setSchema($this->getTableSchema($fields));
    }
  }

  /**
   * Get field names from results of a DESCRIBE query.
   *
   * @param array $fieldsInfo
   *   Array containing thre results of a DESCRIBE query sent to db connection.
   */
  private function getFieldsFromFieldsInfo(array $fieldsInfo) {
    $fields = [];
    foreach ($fieldsInfo as $info) {
      $fields[] = $info->Field;
    }
    return $fields;
  }

  /**
   * Get table schema.
   */
  private function getTableSchema($fields) {
    $schema = [];
    $header = $fields;
    foreach ($header as $field) {
      $schema['fields'][$field] = [
        'type' => "text",
      ];
    }
    return $schema;
  }

}
