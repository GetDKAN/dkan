<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class ValidMetadataFactory implements ContainerInjectionInterface {

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
      $container->get('dkan.metastore.schema_retriever'),
    );
  }

  /**
   * ValidMetadataFactory constructor.
   *
   * @param \Drupal\metastore\SchemaRetrieverInterface $schemaRetriever
   *   Service dkan.metastore.schema_retriever.
   */
  public function __construct(SchemaRetrieverInterface $schemaRetriever) {
    $this->schemaRetriever = $schemaRetriever;
  }

  /**
   * Gets schema retriever.
   *
   * @return \Drupal\metastore\SchemaRetriever
   *   Service metastore.schema_retriever.
   */
  public function getSchemaRetriever() {
    return $this->schemaRetriever;
  }

  /**
   * Converts Json string into RootedJsonData object.
   *
   * @param string|null $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $json_string
   *   Json string.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  public function get(string $schema_id = NULL, string $json_string): RootedJsonData {
    $schema = !empty($schema_id) ? $this->getSchemaRetriever()->retrieve($schema_id) : '{}';
    return new RootedJsonData($json_string, $schema);
  }

}
