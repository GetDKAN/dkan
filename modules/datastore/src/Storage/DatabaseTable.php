<?php

namespace Drupal\datastore\Storage;

use Drupal\Core\Database\Connection;
use Dkan\Datastore\Resource;
use Drupal\common\LoggerTrait;
use Drupal\common\Storage\AbstractDatabaseTable;

/**
 * Database storage object.
 *
 * @see \Dkan\Datastore\Storage\StorageInterface
 */
class DatabaseTable extends AbstractDatabaseTable implements \JsonSerializable {

  use LoggerTrait;

  /**
   * Datastore resource object.
   *
   * @var \Dkan\Datastore\Resource
   */
  private $resource;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   * @param \Dkan\Datastore\Resource $resource
   *   A resource.
   */
  public function __construct(Connection $connection, Resource $resource) {
    // Set resource before calling the parent constructor. The parent calls
    // getTableName which we implement and needs the resource to operate.
    $this->resource = $resource;
    $this->connection = $connection;

    if ($this->tableExist($this->getTableName())) {
      $this->setSchemaFromTable();
    }
  }

  /**
   * Get summary.
   */
  public function getSummary() {
    $columns = $this->getSchema()['fields'];
    $numOfColumns = count($columns);
    $numOfRows = $this->count();
    return new TableSummary($numOfColumns, $columns, $numOfRows);
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return (object) ['resource' => $this->resource];
  }

  /**
   * Hydrate.
   */
  public static function hydrate(string $json) {
    $data = json_decode($json);
    $resource = Resource::hydrate(json_encode($data->resource));

    return new DatabaseTable(\Drupal::service('database'), $resource);
  }

  /**
   * Get the full name of datastore db table.
   *
   * @return string
   *   Table name.
   */
  public function getTableName() {
    if ($this->resource) {
      return "datastore_{$this->resource->getId()}";
    }
    return "datastore_does_not_exist";
  }

  /**
   * Protected.
   */
  protected function prepareData(string $data, string $id = NULL): array {
    $decoded = json_decode($data);
    if ($decoded === NULL) {
      $this->log(
        'datastore_import',
        "Error decoding id:@id, data: @data.",
        ['@id' => $id, '@data' => $data]
      );
      throw new \Exception("Import for {$id} error when decoding {$data}");
    }
    elseif (!is_array($decoded)) {
      $this->log(
        'datastore_import',
        "Array expected while decoding id:@id, data: @data.",
        ['@id' => $id, '@data' => $data]
      );
      throw new \Exception("Import for {$id} returned an error when preparing table header: {$data}");
    }
    return $decoded;
  }

  /**
   * Protected.
   */
  protected function primaryKey() {
    return "record_number";
  }

  /**
   * Protected.
   */
  protected function getNonSerialFields() {
    $fields = parent::getNonSerialFields();
    $index = array_search($this->primaryKey(), $fields);
    if ($index !== FALSE) {
      unset($fields[$index]);
    }
    return $fields;
  }

  /**
   * Set the schema using the existing database table.
   */
  private function setSchemaFromTable() {
    $tableName = $this->getTableName();
    $fieldsInfo = $this->connection->query("DESCRIBE `{$tableName}`")->fetchAll();

    $schema = $this->buildTableSchema($tableName, $fieldsInfo);
    $this->setSchema($schema);
  }

  /**
   * Get table schema.
   *
   * @todo Note that this will brake on PostgresSQL
   */
  private function buildTableSchema($tableName, $fieldsInfo) {
    $schema = ['primaryKey' => NULL, 'fields' => []];
    $canGetComment = method_exists($this->connection->schema(), 'getComment');
    foreach ($fieldsInfo as $info) {
      $name = $info->Field;
      $schema['fields'][$name] = array_filter([
        'name' => $name,
        'description' => $canGetComment ? $this->connection->schema()->getComment($tableName, $name) : '',
        'type' => $this->translateType($info->Type),
        'format' => $this->translateFormat($info->Type),
      ]);
      $schema['primaryKey'] = (isset($info->Key) && $info->Key == 'PRI') ? $name : $schema['primaryKey'];
    }
    return $schema;
  }

  /**
   * Translate the database type into a table schema type.
   *
   * @param string $type
   *   Type returned from the describe query.
   *
   * @return string
   *   Fritionless Table Schema compatible type.
   *
   * @see https://specs.frictionlessdata.io/table-schema
   */
  private function translateType(string $type) {
    // Clean up things like "int(10) unsigned".
    $simpleType = strtok($type, '(');
    $map = [
      'varchar' => 'string',
      'text' => 'string',
      'char' => 'string',
      'tinytext' => 'string',
      'mediumtext' => 'string',
      'longtext' => 'string',
      'int' => 'number',
      'tinyint' => 'number',
      'smallint' => 'number',
      'mediumint' => 'number',
      'bigint' => 'number',
      'float' => 'number',
      'double' => 'number',
      'decimal' => 'number',
      'numeric' => 'number',
      'date' => 'date',
      'datetime' => 'datetime',
    ];

    return $map[$simpleType] ?? $type;
  }

  /**
   * Add format to any database types requiring it.
   *
   * @param string $type
   *   Database column type. Currently only works with datetime and timestamps.
   *
   * @return string|null
   *   If available, a string to be used in the field format.
   *
   * @see https://specs.frictionlessdata.io/table-schema/
   */
  private function translateFormat(string $type) {
    if (in_array($type, ['datetime', 'timestamp'])) {
      return '%Y-%m-%d %H:%M:%S';
    }
    return NULL;
  }

}
