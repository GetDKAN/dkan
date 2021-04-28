<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class RootedJsonDataFactory implements ContainerInjectionInterface {

  /**
   * Schema retriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
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
   * RootedJsonDataFactory constructor.
   *
   * @param \Drupal\metastore\SchemaRetriever $schemaRetriever
   *   dkan.metastore.schema_retriever service.
   */
  public function __construct(SchemaRetriever $schemaRetriever) {
    $this->schemaRetriever = $schemaRetriever;
  }

  /**
   * @return \Drupal\metastore\SchemaRetriever
   */
  public function getSchemaRetriever() {
    return $this->schemaRetriever;
  }

  /**
   * Converts Json string into RootedJsonData object.
   *
   * @param string|NULL $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $json_string
   *   Json string.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  public function createRootedJsonData(string $schema_id = NULL, string $json_string): RootedJsonData {
    $schema = !empty($schema_id) ? $this->getSchemaRetriever()->retrieve($schema_id) : '{}';
    return new RootedJsonData($json_string, $schema);
  }

}
