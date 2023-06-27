<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\common\EventDispatcherTrait;
use Drupal\Core\Database\SchemaObjectExistsException;

/**
 * Base class for database storage methods.
 */
abstract class AbstractDatabaseTable implements DatabaseTableInterface {
  use EventDispatcherTrait;

  const EVENT_TABLE_CREATE = 'dkan_common_table_create';

  /**
   * A schema. Should be a drupal schema array.
   *
   * @var array
   */
  protected $schema;

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
  public function primaryKey() {
    return 'id';
  }

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
   * {@inheritdoc}
   */
  public function retrieve(string $id) {
    $this->setTable();

    $select = $this->connection->select($this->getTableName(), 't')
      ->fields('t', array_keys($this->getSchema()['fields']))
      ->condition($this->primaryKey(), $id);

    $statement = $select->execute();

    // The docs do not mention it, but fetch can return false.
    $return = (isset($statement)) ? $statement->fetch() : NULL;

    return ($return === FALSE) ? NULL : $return;
  }

  /**
   * {@inheritdoc}
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
          'The number of fields and data given do not match: fields - '
            . json_encode($fields) . ' data - ' . json_encode($data)
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
        throw new \Exception('The number of fields and data given do not match: fields - ' .
          json_encode($fields) . ' data - ' . json_encode($datum));
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
    return $this->connection->delete($tableName)
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
   * @param bool $fetch
   *   Fetch the rows if true, just return the result statement if not.
   *
   * @return array|\Drupal\Core\Database\StatementInterface
   *   Array of results if $fetch is true, otherwise result of
   *   Select::execute() (prepared Statement object or null).
   */
  public function query(Query $query, string $alias = 't', $fetch = TRUE) {
    $this->setTable();
    $query->collection = $this->getTableName();
    $selectFactory = new SelectFactory($this->connection, $alias);
    $db_query = $selectFactory->create($query);

    try {
      $result = $db_query->execute();
    }
    catch (DatabaseExceptionWrapper $e) {
      throw new \Exception($this->sanitizedErrorMessage($e->getMessage()));
    }

    return $fetch ? $result->fetchAll() : $result;
  }

  /**
   * Create a minimal error message that does not leak database information.
   */
  protected function sanitizedErrorMessage(string $unsanitizedMessage) {
    // Insert portions of exception messages you want caught here.
    $messages = [
      // Portion of the message => User friendly message.
      'Column not found' => 'Column not found',
      'Mixing of GROUP columns' => 'You may not mix simple properties and aggregation expressions in a single query. If one of your properties includes an expression with a sum, count, avg, min or max operator, remove other properties from your query and try again',
      'Can\'t find FULLTEXT index matching the column list' => 'You have attempted a fulltext match against a column that is not indexed for fulltext searching',
    ];
    foreach ($messages as $portion => $message) {
      if (strpos($unsanitizedMessage, $portion) !== FALSE) {
        return $message . '.';
      }
    }
    return 'Database internal error.';
  }

  /**
   * Create the table in the db if it does not yet exist.
   *
   * @throws \Exception
   *   Throws an exception if the schema was not already set.
   */
  protected function setTable() {
    if (!$this->tableExist($table_name = $this->getTableName())) {
      if ($schema = $this->schema) {
        try {
          $this->tableCreate($table_name, $schema);
        }
        catch (SchemaObjectExistsException $e) {
          // Table already exists, which is totally OK. Other throwables find
          // their way out to the caller.
        }
      }
      else {
        throw new \Exception('Could not instantiate the table due to a lack of schema.');
      }
    }
  }

  /**
   * Destroy.
   *
   * Drop the database table.
   */
  public function destruct() {
    if ($this->tableExist($this->getTableName())) {
      $this->connection->schema()->dropTable($this->getTableName());
    }
  }

  /**
   * Check for existence of a table name.
   */
  protected function tableExist($table_name): bool {
    return $this->connection->schema()->tableExists($table_name);
  }

  /**
   * Create a table given a name and schema.
   *
   * @throws \Throwable
   */
  protected function tableCreate($table_name, $schema) {
    // Opportunity to further alter the schema before table creation.
    $schema = $this->dispatchEvent(self::EVENT_TABLE_CREATE, $schema);

    $this->connection->schema()->createTable($table_name, $schema);
  }

  /**
   * Set the schema using the existing database table.
   */
  protected function setSchemaFromTable() {
    $fields_info = $this->connection->query('DESCRIBE {' . $this->getTableName() . '}')->fetchAll();
    if (empty($fields_info)) {
      return;
    }

    foreach ($fields_info as $info) {
      $fields[] = $info->Field;
    }
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
   * Get table schema.
   */
  private function getTableSchema($fields) {
    $schema = [];
    $header = $fields;
    foreach ($header as $field) {
      $schema['fields'][$field] = [
        'type' => 'text',
      ];
    }
    return $schema;
  }

  /**
   * Clean up and set the schema for SQL storage.
   */
  private function cleanSchema(): void {
    $cleanSchema = $this->schema;
    $cleanSchema['fields'] = [];
    foreach ($this->schema['fields'] as $field => $info) {
      $new = preg_replace('/[^A-Za-z0-9_ ]/', '', $field);
      $new = trim($new);
      $new = strtolower($new);
      $new = str_replace(' ', '_', $new);

      $mysqlMaxColLength = 64;
      if (strlen($new) > $mysqlMaxColLength) {
        $strings = str_split($new, $mysqlMaxColLength - 5);
        $token = $this->generateToken($field);
        $new = $strings[0] . "_{$token}";
      }

      if ($field != $new) {
        $info['description'] = $field;
      }

      $cleanSchema['fields'][$new] = $info;
    }

    $this->schema = $cleanSchema;
  }

  /**
   * Define a schema for the table.
   *
   * @param array $schema
   *   A schema. Should be a drupal schema array.
   */
  public function setSchema(array $schema): void {
    $this->schema = $schema;
    $this->cleanSchema();
  }

  /**
   * Get the schema for this table.
   *
   * @return array
   *   A schema array.
   */
  public function getSchema(): array {
    return $this->schema;
  }

  /**
   * Generate a short 4-character token for a field, to help truncate.
   *
   * @param string $field
   *   A field name from the schema.
   *
   * @return string|false
   *   The four-character token, or false if failed.
   */
  public function generateToken(string $field) {
    $md5 = md5($field);
    return substr($md5, 0, 4);
  }

}
