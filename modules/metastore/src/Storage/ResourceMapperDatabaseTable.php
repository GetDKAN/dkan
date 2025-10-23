<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Database\Connection;
use Drupal\common\Storage\AbstractDatabaseTable;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Database storage object.
 *
 * @deprecated Use resource_mapping entity type instead.
 *
 * @see \Drupal\metastore\Entity\ResourceMapping
 */
class ResourceMapperDatabaseTable extends AbstractDatabaseTable {

  /**
   * Resource mapper database table schema.
   *
   * @var array
   */
  protected $schema;

  /**
   * DKAN logger channel service.
   */
  private LoggerInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    Connection $connection,
    LoggerInterface $loggerChannel,
    EventDispatcherInterface $eventDispatcher
  ) {
    parent::__construct($connection, $eventDispatcher);
    $this->logger = $loggerChannel;

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
   * {@inheritdoc}
   */
  public function getTableName() {
    return "dkan_metastore_resource_mapper";
  }

  /**
   * Protected.
   */
  protected function prepareData(string $data, ?string $id = NULL): array {
    $decoded = json_decode($data);

    foreach (['filePath' => 'filepath', 'mimeType' => 'mimetype'] as $old => $new) {
      $decoded->{$new} = $decoded->{$old};
      unset($decoded->{$old});
    }

    if ($decoded === NULL) {
      $this->logger->error('Error decoding id:@id, data: @data.', [
        '@id' => $id,
        '@data' => $data,
      ]);
      throw new \Exception('Import for ' . $id . ' error when decoding ' . $data);
    }
    elseif (!is_object($decoded)) {
      $this->logger->error('Object expected while decoding id:@id, data: @data.', [
        '@id' => $id,
        '@data' => $data,
      ]);
      throw new \Exception('Import for ' . $id . ' returned an error when preparing table header: ' . $data);
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
