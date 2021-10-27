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
  public function primaryKey() {
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
  protected function setSchemaFromTable() {
    $tableName = $this->getTableName();
    $fieldsInfo = $this->connection->query("DESCRIBE `{$tableName}`")->fetchAll();

    $schema = $this->buildTableSchema($tableName, $fieldsInfo);
    $this->setSchema($schema);
  }

  /**
   * {@inheritdoc}
   */
  public function setSchema($schema) {
    $fields = $schema['fields'];
    $new_field = [
      $this->primaryKey() =>
      [
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE,
      ],
    ];
    $fields = array_merge($new_field, $fields);

    $schema['fields'] = $fields;
    $schema['primary key'] = [$this->primaryKey()];
    parent::setSchema($schema);
  }

  /**
   * Get table schema.
   *
   * @todo Note that this will break on PostgresSQL
   */
  protected function buildTableSchema($tableName, $fieldsInfo) {
    $canGetComment = method_exists($this->connection->schema(), 'getComment');
    foreach ($fieldsInfo as $info) {
      $name = $info->Field;
      $schema['fields'][$name] = $this->translateType($info->Type, ($info->Extra ?? NULL));
      $schema['fields'][$name] += [
        'description' => $canGetComment ? $this->connection->schema()->getComment($tableName, $name) : '',
      ];
      $schema['fields'][$name] = array_filter($schema['fields'][$name]);
    }
    return $schema ?? ['fields' => []];
  }

  /**
   * Translate the database type into a table schema type.
   *
   * @param string $type
   *   Type returned from the describe query.
   * @param mixed $extra
   *   Additional information for column.
   *
   * @return array
   *   Drupal Schema array.
   *
   * @see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Database!database.api.php/group/schemaapi/9.2.x
   */
  protected function translateType(string $type, $extra = NULL) {
    // Clean up things like "int(10) unsigned".
    $db_type = strtok($type, '(');
    $driver = $this->connection->driver() ?? 'mysql';

    preg_match('#\((.*?)\)#', $type, $match);
    $length = $match[1] ?? NULL;
    $length = $length ? (int) $length : $length;

    $map = array_flip(array_map('strtolower', $this->connection->schema()->getFieldTypeMap()));

    $fullType = explode(':', ($map[$db_type] ?? 'varchar'));
    // Set type to serial if auto-increment, else use mapped type.
    $type = ($fullType[0] == 'int' && $extra == 'auto_increment') ? 'serial' : $fullType[0];
    $unsigned = ($type == 'serial') ? TRUE : NULL;
    $notNull = ($type == 'serial') ? TRUE : NULL;
    // Ignore size if "normal" or unset.
    $size = (isset($fullType[1]) && $fullType[1] != 'normal') ? $fullType[1] : NULL;

    return [
      'type' => $type,
      'length' => $length,
      'size' => $size,
      'unsigned' => $unsigned,
      'not null' => $notNull,
      "{$driver}_type" => $db_type,
    ];
  }

}
