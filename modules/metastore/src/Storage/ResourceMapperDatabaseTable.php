<?php

namespace Drupal\metastore\Storage;

use Drupal\common\LoggerTrait;
use Drupal\common\Storage\AbstractDatabaseTable;
use Drupal\Core\Database\Connection;
use Psr\Log\LogLevel;

/**
 * Database storage object.
 */
class ResourceMapperDatabaseTable extends AbstractDatabaseTable {
  use LoggerTrait;

  /**
   * Resource mapper database table schema.
   *
   * @var array
   */
  protected $schema;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    parent::__construct($connection);

    $schema = [];

    foreach ($this->getHeader() as $field) {
      $schema['fields'][$field] = [
        'type' => "text",
      ];
    }

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
    $this->setSchema($schema);
  }

  /**
   * Gets header fields.
   *
   * @return array
   *   Header array.
   */
  protected function getHeader() : array {
    return [
      'identifier',
      'version',
      'filePath',
      'perspective',
      'mimeType',
      'checksum',
    ];
  }

  /**
   * Get the full name of datastore db table.
   *
   * @return string
   *   Table name.
   */
  protected function getTableName() {
    return "dkan_metastore_resource_mapper";
  }

  /**
   * Protected.
   */
  protected function prepareData(string $data, string $id = NULL): array {
    $decoded = json_decode($data);

    foreach (['filePath' => 'filepath', 'mimeType' => 'mimetype'] as $old => $new) {
      $decoded->{$new} = $decoded->{$old};
      unset($decoded->{$old});
    }

    if ($decoded === NULL) {
      $this->log(
        'dkan_metastore_filemapper',
        "Error decoding id:@id, data: @data.",
        ['@id' => $id, '@data' => $data],
        LogLevel::ERROR
      );
      throw new \Exception("Import for {$id} error when decoding {$data}");
    }
    elseif (!is_object($decoded)) {
      $this->log(
        'dkan_metastore_filemapper',
        "Object expected while decoding id:@id, data: @data.",
        ['@id' => $id, '@data' => $data],
        LogLevel::ERROR
      );
      throw new \Exception("Import for {$id} returned an error when preparing table header: {$data}");
    }
    return (array) $decoded;
  }

  /**
   * Protected.
   */
  public function primaryKey() {
    return "id";
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

}
