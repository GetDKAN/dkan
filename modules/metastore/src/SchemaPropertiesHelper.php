<?php

namespace Drupal\metastore;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper for metastore schema properties.
 *
 * @package Drupal\metastore
 */
class SchemaPropertiesHelper implements ContainerInjectionInterface {

  /**
   * SchemaRetriever service.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  protected $schemaRetriever;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_retriever')
    );
  }

  /**
   * Public.
   */
  public function __construct(SchemaRetriever $schemaRetriever) {
    $this->schemaRetriever = $schemaRetriever;
  }

  /**
   * Retrieve schema properties.
   *
   * @return array
   *   List of schema properties' title and description.
   */
  public function retrieveSchemaProperties(): array {
    // Create a json object from our schema.
    $schema = $this->schemaRetriever->retrieve('dataset');
    $schema_object = json_decode($schema);

    // Build a list of the schema properties' title and description.
    $property_list = [];
    foreach ($schema_object->properties as $property_id => $property_object) {
      if (isset($property_object->title)) {
        $property_list[$property_id] = "{$property_object->title} ({$property_id})";
      }
      else {
        $property_list[$property_id] = ucfirst($property_id);
      }
    }

    return $property_list;
  }

}
