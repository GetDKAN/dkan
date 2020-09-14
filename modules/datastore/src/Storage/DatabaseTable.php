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
    parent::__construct($connection);
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
  protected function getTableName() {
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
   * Overriden.
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

}
