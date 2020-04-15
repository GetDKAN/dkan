<?php

namespace Drupal\dkan_metastore\Factory;

use Contracts\FactoryInterface;
use Drupal\dkan_data\Storage\Data;
use Drupal\dkan_schema\SchemaRetriever;
use Sae\Sae as Engine;

/**
 * Class Sae.
 */
class Sae implements FactoryInterface {

  /**
   * Engines.
   *
   * @var array
   */
  private $engines = [];

  /**
   * Storage.
   *
   * @var \Drupal\dkan_data\Storage\Data
   */
  private $storage;

  /**
   * Schema retriever.
   *
   * @var \Drupal\dkan_schema\SchemaRetriever
   */
  private $schemaRetriever;

  /**
   * Constructs a new Sae.
   *
   * @param \Drupal\dkan_schema\SchemaRetriever $schemaRetriever
   *   Schema retriever.
   * @param \Drupal\dkan_data\Storage\Data $storage
   *   Data.
   */
  public function __construct(SchemaRetriever $schemaRetriever, Data $storage) {
    $this->schemaRetriever = $schemaRetriever;
    $this->storage = $storage;
  }

  /**
   * Get instance.
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($this->engines[$identifier])) {
      $this->engines[$identifier] = new Engine($this->getStorage($identifier), $this->getJsonSchema($identifier));
    }
    return $this->engines[$identifier];
  }

  /**
   * Get Json Schema.
   *
   * @return string
   *   Json schema.
   */
  private function getJsonSchema($schema_id) {

    // @Todo: mechanism to validate against additional schemas. For now,
    // validate against the empty object, as it accepts any valid json.
    if ($schema_id != 'dataset') {
      return '{ }';
    }

    return $this->schemaRetriever->retrieve('dataset');
  }

  /**
   * Get Storage.
   *
   * @return \Drupal\dkan_data\Storage\Data
   *   Dataset
   */
  private function getStorage($schema_id) {
    $this->storage->setSchema($schema_id);
    return $this->storage;
  }

}
