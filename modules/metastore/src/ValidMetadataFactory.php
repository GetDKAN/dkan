<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\Service\Uuid5;
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
      $container->get('metastore.schema_retriever'),
    );
  }

  /**
   * ValidMetadataFactory constructor.
   *
   * @param \Drupal\metastore\SchemaRetriever $schemaRetriever
   *   Service dkan.metastore.schema_retriever.
   */
  public function __construct(SchemaRetriever $schemaRetriever) {
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
   * @param string $json_string
   *   Json string.
   * @param string|null $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param array $options
   *   Options array.
   *
   * @return \RootedData\RootedJsonData
   *   RootedJsonData object.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  public function get(string $json_string, $schema_id = NULL, array $options = []): RootedJsonData {

    // Add identifier for new objects if necessary.
    if (isset($options['method']) && $options['method'] == 'POST') {
      $data = json_decode($json_string);
      if (!isset($data->identifier)) {
        $json_string = $this->addIdentifier($schema_id, $json_string);
      }
    }

    $schema = !empty($schema_id) ? $this->getSchemaRetriever()->retrieve($schema_id) : '{}';
    return new RootedJsonData($json_string, $schema);
  }

  /**
   * Adds identifier to JSON payload.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $json_string
   *   Json string with no identifier.
   *
   * @return string
   *   Json string with the identifier.
   */
  private function addIdentifier(string $schema_id, string $json_string): string {
    // We coerce to an object, because the data might already be an object, or
    // it could be an array. If $json_string is empty, json_decode() will
    // return NULL, which always coerces to a new object with no properties.
    $json_data = (object) json_decode($json_string);
    $uuid5 = new Uuid5();
    $json_data->identifier = $uuid5->generate($schema_id, $json_string);
    return json_encode($json_data);
  }

}
