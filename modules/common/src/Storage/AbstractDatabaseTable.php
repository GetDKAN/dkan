<?php

namespace Drupal\common\Storage;

use Dkan\Datastore\Storage\Database\SqlStorageTrait;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\common\EventDispatcherTrait;

/**
 * AbstractDatabaseTable class.
 */
abstract class AbstractDatabaseTable implements DatabaseTableInterface {
  use SqlStorageTrait;
  use EventDispatcherTrait;

  const EVENT_TABLE_CREATE = 'dkan_common_table_create';

  /**
   * Drupal DB connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
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
  abstract protected function prepareData(string $data, string $id = NULL): array;

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
   * @inheritdoc
   */
  public function retrieve(string $id) {
    $this->setTable();

    /* @var \Drupal\Core\Database\Query\Select $select */
    $select = $this->connection->select($this->getTableName(), 't')
      ->fields('t', array_keys($this->getSchema()['fields']))
      ->condition($this->primaryKey(), $id);

    /* @var \Drupal\Core\Database\StatementInterface $statement */
    $statement = $select->execute();

    // The docs do not mention it, but fetch can return false.
    $return = (isset($statement)) ? $statement->fetch() : NULL;

    return ($return === FALSE) ? NULL : $return;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
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

    $result = array_map(function ($item) {
      return $item->{$this->primaryKey()};
    }, $result);

    return $result;
  }

  /**
   * Store data.
   */
  public function store($data, string $id = NULL): string {
    $this->setTable();

    $existing = (isset($id)) ? $this->retrieve($id) : NULL;

    $data = $this->prepareData($data, $id);

    $returned_id = NULL;

    if ($existing === NULL) {
      $fields = $this->getNonSerialFields();

      if (count($fields) != count($data)) {
        throw new \Exception(
          "The number of fields and data given do not match: fields - "
            . json_encode($fields) . " data - " . json_encode($data)
        );
      }

      $q = $this->connection->insert($this->getTableName());
      $q->fields($fields);
      $q->values($data);
      $returned_id = $q->execute();
    }
    else {
      $q = $this->connection->update($this->getTableName());
      $q->fields($data)
        ->condition($this->primaryKey(), $id)
        ->execute();
    }

    return ($returned_id) ? "$returned_id" : "{$id}";
  }

  /**
   * Prepare to store possibly multiple values.
   *
   * @param array $data
   *   Array of values to be inserted into the database.
   *
   * @return string|null
   *   Last record id inserted into the database.
   */
  public function storeMultiple(array $data) {
    $this->setTable();

    $fields = $this->getNonSerialFields();

    $q = $this->connection->insert($this->getTableName());
    $q->fields($fields);
    foreach ($data as $datum) {
      $datum = $this->prepareData($datum);
      if (count($fields) != count($datum)) {
        throw new \Exception("The number of fields and data given do not match: fields - " .
          json_encode($fields) . " data - " . json_encode($datum));
      }
      $q->values($datum);
    }
    return $q->execute();
  }

  /**
   * Private.
   */
  protected function getNonSerialFields() {
    $fields = [];
    foreach ($this->schema['fields'] as $field => $info) {
      if ($info['type'] != 'serial') {
        $fields[] = $field;
      }
    }
    return $fields;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function remove(string $id) {
    $tableName = $this->getTableName();
    $this->connection->delete($tableName)
      ->condition($this->primaryKey(), $id)
      ->execute();
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
   * @param \Drupal\common\Storage\Query $query
   *   Query object.
   * @param string $alias
   *   (Optional) alias for primary table.
   */
  public function query(Query $query, string $alias = 't'): array {
    $this->setTable();
    $query->collection = $this->getTableName();
    $selectFactory = new SelectFactory($this->connection, $alias);
    $db_query = $selectFactory->create($query);

    try {
      $result = $db_query->execute()->fetchAll();
    }
    catch (DatabaseExceptionWrapper $e) {
      throw new \Exception($this->sanitizedErrorMessage($e->getMessage()));
    }

    return $result;
  }

  /**
   * Create a minimal error message that does not leak database information.
   */
  private function sanitizedErrorMessage(string $unsanitizedMessage) {
    // Insert portions of exception messages you want caught here.
    $messages = [
      'Column not found',
    ];
    foreach ($messages as $message) {
      if (strpos($unsanitizedMessage, $message) !== FALSE) {
        return $message . ".";
      }
    }
    return "Database internal error.";
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
    // Opportunity to alter the schema before table creation.
    $schema = $this->dispatchEvent(self::EVENT_TABLE_CREATE, $schema);
    $this->connection->schema()->createTable($table_name, $schema);
  }

  /**
   * Set the schema using the existing database table.
   */
  private function setSchemaFromTable() {
    $fields_info = $this->connection->query("DESCRIBE `{$this->getTableName()}`")->fetchAll();
    if (empty($fields_info)) {
      return;
    }

    $fields = $this->getFieldsFromFieldsInfo($fields_info);
    $schema = $this->getTableSchema($fields);
    if (method_exists($this->connection->schema(), 'getComment')) {
      foreach ($schema['fields'] as $fieldName => $info) {
        $newInfo = $info;
        $newInfo['description'] = $this->connection->schema()->getComment($this->getTableName(), $fieldName);
        $schema['fields'][$fieldName] = $newInfo;
      }
    }
    $this->setSchema($schema);
  }

  /**
   * Get field names from results of a DESCRIBE query.
   *
   * @param array $fieldsInfo
   *   Array containing three results of a DESCRIBE query sent to db connection.
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
