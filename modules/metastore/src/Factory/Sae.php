<?php

namespace Drupal\metastore\Factory;

use Contracts\FactoryInterface;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\SchemaRetriever;
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
   * Data factory.
   *
   * @var \Drupal\metastore\Storage\DataFactory
   */
  private $factory;

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  private $schemaRetriever;

  /**
   * Constructs a new Sae.
   *
   * @param \Drupal\metastore\SchemaRetriever $schemaRetriever
   *   Schema retriever.
   * @param \Drupal\metastore\Storage\DataFactory $factory
   *   A Data factory.
   */
  public function __construct(SchemaRetriever $schemaRetriever, DataFactory $factory) {
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

    // @todo mechanism to validate against additional schemas. For now,
    // validate against the empty object, as it accepts any valid json.
    if (!in_array($schema_id, $schemas)) {
      return '{ }';
    }

    return $this->schemaRetriever->retrieve($schema_id);
  }

  /**
   * Get Storage.
   *
   * @return \Drupal\metastore\Storage\Data
   *   Dataset
   */
  private function getStorage($schema_id) {
    return $this->factory->getInstance($schema_id);
  }

}
