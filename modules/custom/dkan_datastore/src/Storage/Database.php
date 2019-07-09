<?php

namespace Drupal\dkan_datastore\Storage;

use Contracts\Schemed;
use Dkan\Datastore\Storage\Storage;
use Drupal\Core\Database\Connection;
use Dkan\Datastore\Storage\Database\Query\Insert;
use Dkan\Datastore\Resource;


/**
 * @codeCoverageIgnore
 */
class Database implements Storage, Schemed {
  private $connection;

  /** @var Resource */
  private $resource;
  private $schema;

  /**
   *
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public function setResource(Resource $resource) {
    $this->resource = $resource;
    if (!$this->schema && $this->tableExist($this->getTableName())) {
      $this->setSchemaFromTable();
    }
  }

  private function setSchemaFromTable() {
    $fields_info = $this->connection->query("DESCRIBE `{$this->getTableName()}`")->fetchAll();
    if (!empty($fields_info)) {
      $fields = $this->getFieldsFromFieldsInfo($fields_info);
      $this->setSchema($this->getTableSchema($fields));
    }
  }

  private function getFieldsFromFieldsInfo($fields_info) {
    $fields = [];
    foreach ($fields_info as $info) {
      $fields[] = $info->Field;
    }
    return $fields;
  }

  public function retrieveAll(): array
  {
    // TODO: Implement retrieveAll() method.
  }

  public function retrieve(string $id): ?string
  {
    // TODO: Implement retrieve() method.
  }

  public function store(string $data, string $id = null): string
  {
    $this->checkRequirementsAndPrepare();
    $data = json_decode($data);
    $insert = new Insert($this->getTableName());
    $insert->fields(array_keys($this->schema['fields']));
    $insert->values($data);
    $this->insert($insert);
    return "SUCCESS";
  }

  public function remove(string $id)
  {
    // TODO: Implement remove() method.
  }

  /**
   *
   */
  public function count(): int
  {
    if ($this->tableExist($this->getTableName())) {
      $query = db_select($this->getTableName());
      return $query->countQuery()->execute()->fetchField();
    }
    throw new \Exception("Table {$this->getTableName()} does not exist.");
  }

  private function getTableName() {
    if ($this->resource) {
      return "dkan_datastore_{$this->resource->getId()}";
    }
    else {
      return "";
    }
  }

  /**
   *
   */
  public function query(Query $query): array {
    $db_query = $this->connection->select($this->getTableName(), 't');
    $db_query->fields('t', $query->properties);

    foreach ($query->conditions as $property => $value) {
      $db_query->condition($property, $value, "LIKE");
    }

    foreach ($query->sort['ASC'] as $property) {
      $db_query->orderBy($property);
    }

    foreach ($query->sort['DESC'] as $property) {
      $db_query->orderBy($property, 'DESC');
    }

    if ($query->limit) {
      if ($query->offset) {
        $db_query->range($query->offset, $query->limit);
      }
      else {
        $db_query->range(1, $query->limit);
      }
    }
    elseif ($query->offset) {
      $db_query->range($query->limit);
    }

    if ($query->count) {
      $db_query = $db_query->countQuery();
    }

    $result = $db_query->execute()->fetchAll();

    return $result;
  }

  public function setSchema($schema)
  {
    $this->schema = $schema;
  }

  public function getSchema() {
    return $this->schema;
  }

  private function checkRequirementsAndPrepare() {
    if (!$this->resource) {
      throw new \Exception("Resource is required.");
    }

    if (!$this->schema) {
      throw new \Exception("Schema is required.");
    }

    if (!$this->tableExist($this->getTableName())) {
      $this->tableCreate($this->getTableName(), $this->schema);
    }
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

  /**
   *
   */
  private function tableExist($table_name) {
    $exists = $this->connection->schema()->tableExists($table_name);
    return $exists;
  }

  /**
   *
   */
  private function tableCreate($table_name, $schema) {
    db_create_table($table_name, $schema);
  }

  /**
   *
   */
  private function tableDrop($table_name) {
    $this->connection->schema()->dropTable($table_name);
  }

  /**
   *
   */
  private function insert(Insert $query) {
    if ($this->tableExist($query->tableName)) {
      $q = db_insert($query->tableName);
      $q->fields($query->fields);
      foreach ($query->values as $values) {
        $q->values($values);
      }
      $q->execute();
    }
  }
}
