<?php

namespace Drupal\datastore\Storage;

use Drupal\common\LoggerTrait;
use Drupal\common\Storage\AbstractDatabaseTable;
use Drupal\Core\Database\Connection;
use Drupal\datastore\DatastoreResource;

/**
 * Database storage object.
 *
 * @see \Drupal\common\Storage\DatabaseTableInterface
 */
class DatabaseTable extends AbstractDatabaseTable implements \JsonSerializable {

  use LoggerTrait;

  /**
   * Datastore resource object.
   *
   * @var \Drupal\datastore\DatastoreResource
   */
  private $resource;

  /**
   * Constructor method.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   * @param \Drupal\datastore\DatastoreResource $resource
   *   A resource.
   */
  public function __construct(Connection $connection, DatastoreResource $resource) {
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
    $schema = $this->getSchema();
    $columns = $schema['fields'];
    $indexes = $schema['indexes'] ?? NULL;
    $fulltext_indexes = $schema['fulltext indexes'] ?? NULL;
    $numOfColumns = count($columns);
    $numOfRows = $this->count();
    return new TableSummary(
      $numOfColumns,
      $columns,
      $indexes,
      $fulltext_indexes,
      $numOfRows);
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return (object) ['resource' => $this->resource];
  }

  /**
   * Get the full name of datastore db table.
   *
   * @return string
   *   Table name.
   */
  public function getTableName() {
    if ($this->resource) {
      return 'datastore_' . $this->resource->getId();
    }
    return 'datastore_does_not_exist';
  }

  /**
   * Protected.
   */
  protected function prepareData(string $data, string $id = NULL): array {
    $decoded = json_decode($data);
    if ($decoded === NULL) {
      $this->log(
        'datastore_import',
        'Error decoding id:@id, data: @data.',
        ['@id' => $id, '@data' => $data]
      );
      throw new \Exception('Import for ' . $id . ' error when decoding ' . $data);
    }
    elseif (!is_array($decoded)) {
      $this->log(
        'datastore_import',
        'Array expected while decoding id:@id, data: @data.',
        ['@id' => $id, '@data' => $data]
      );
      throw new \Exception('Import for ' . $id . ' returned an error when preparing table header: ' . $data);
    }
    return $decoded;
  }

  /**
   * Protected.
   */
  public function primaryKey() {
    return 'record_number';
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
    $fieldsInfo = $this->connection->query('DESCRIBE {' . $tableName . '}')->fetchAll();

    $schema = $this->buildTableSchema($tableName, $fieldsInfo);
    $this->setSchema($schema);
  }

  /**
   * {@inheritdoc}
   */
  public function setSchema($schema): void {
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
   * Get the table schema in Drupal Schema API format.
   *
   * NOTE: This will likely fail on any db driver other than mysql.
   *
   * @param string $tableName
   *   The table name.
   * @param array $fieldsInfo
   *   Array of fields info from DESCRIBE query.
   *
   * @return array
   *   Full Drupal Schema API array.
   */
  protected function buildTableSchema(string $tableName, array $fieldsInfo) {
    // Add descriptions to schema from column comments.
    $canGetComment = method_exists($this->connection->schema(), 'getComment');
    $schema = ['fields' => []];
    foreach ($fieldsInfo as $info) {
      $name = $info->Field;
      $schema['fields'][$name] = $this->translateType($info->Type, ($info->Extra ?? NULL));
      $schema['fields'][$name] += [
        'description' => $canGetComment ? $this->connection->schema()->getComment($tableName, $name) : '',
      ];
      $schema['fields'][$name] = array_filter($schema['fields'][$name]);
    }
    // Add index information to schema if available.
    $this->addIndexInfo($schema);

    return $schema;
  }

  /**
   * Add index information to table schema.
   *
   * @param array $schema
   *   Drupal Schema API array.
   */
  protected function addIndexInfo(array &$schema): void {
    if ($this->connection->getConnectionOptions()['driver'] != 'mysql') {
      return;
    }

    $indexInfo = $this->connection->query('SHOW INDEXES FROM  {' . $this->getTableName() . '}')->fetchAll();
    foreach ($indexInfo as $info) {
      // Primary key is handled elsewhere.
      if ($info->Key_name == 'PRIMARY') {
        continue;
      }
      // Deviating slightly from Drupal Schema API to specify fulltext indexes.
      $indexes_key = $info->Index_type == 'FULLTEXT' ? 'fulltext indexes' : 'indexes';
      $name = $info->Key_name;
      $schema[$indexes_key][$name][] = $info->Column_name;
    }
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
      $driver . '_type' => $db_type,
    ];
  }

}
