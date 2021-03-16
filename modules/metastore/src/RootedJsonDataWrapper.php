<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class RootedJsonDataWrapper implements ContainerInjectionInterface {

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetrieverInterface
   */
  private $schemaRetriever;

  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('metastore.schema_retriever'),
    );
  }

  /**
   * RootedJsonDataWrapper constructor.
   *
   * @param \Drupal\metastore\SchemaRetrieverInterface $schemaRetriever
   *   dkan.metastore.schema_retriever service.
   */
  public function __construct(SchemaRetrieverInterface $schemaRetriever) {
    $this->schemaRetriever = $schemaRetriever;
  }

  /**
   * Converts Json string into RootedJsonData object.
   *
   * @param \Drupal\metastore\string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Drupal\metastore\string $json_string
   *   Json string.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  public function createRootedJsonData(string $schema_id, string $json_string): RootedJsonData {
    $schema = $this->schemaRetriever->retrieve($schema_id);
    return new RootedJsonData($json_string, $schema);
  }

}
