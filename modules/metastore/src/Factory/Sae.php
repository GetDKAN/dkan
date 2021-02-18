<?php

namespace Drupal\metastore\Factory;

use Contracts\FactoryInterface;
use Drupal\metastore\SchemaRetrieverInterface;
use Drupal\metastore\Sae\Sae as Engine;
use Drupal\metastore\Storage\MetastoreStorageFactoryInterface;

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
   * Data factory.
   *
   * @var \Drupal\metastore\Storage\NodeStorageFactory
   */
  private $factory;

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetrieverInterface
   */
  private $schemaRetriever;

  /**
   * Constructs a new Sae.
   *
   * @param \Drupal\metastore\FileSchemaRetriever $schemaRetriever
   *   Schema retriever.
   * @param \Drupal\metastore\Storage\MetastoreStorageFactoryInterface $factory
   *   A Data factory.
   */
  public function __construct(SchemaRetrieverInterface $schemaRetriever, MetastoreStorageFactoryInterface $factory) {
    $this->schemaRetriever = $schemaRetriever;
    $this->factory = $factory;
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
    $schemas = $this->schemaRetriever->getAllIds();

    // @Todo: mechanism to validate against additional schemas. For now,
    // validate against the empty object, as it accepts any valid json.
    if (!in_array($schema_id, $schemas)) {
      return '{ }';
    }

    return $this->schemaRetriever->retrieve($schema_id);
  }

  /**
   * Get Storage.
   *
   * @return \Drupal\metastore\Storage\AbstractEntityStorage
   *   Dataset
   */
  private function getStorage($schema_id) {
    return $this->factory->getInstance($schema_id);
  }

}
