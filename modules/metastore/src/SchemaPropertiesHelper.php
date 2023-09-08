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
   * Retrieve dataset schema properties.
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

  /**
   * Retrieve all string schema properties.
   *
   * @return array
   *   List of schema properties' title and description.
   */
  public function retrieveStringSchemaProperties(): array {
    // Create a json object from our schema.
    $schema = $this->schemaRetriever->retrieve('dataset');
    $schema_object = json_decode($schema);

    return $this->buildPropertyList($schema_object->properties);
  }

  /**
   * Build a list of schema properties.
   *
   * @param mixed $input
   *   Object we're parsing.
   * @param string $parent
   *   Schema for this object
   * @param array $property_list
   *   Array we're building of schema properties.
   *
   * @return array
   *   List of schema properties' title and description.
   */
  private function buildPropertyList(mixed $input, string $parent = 'dataset', array &$property_list = []): array {
    foreach ($input as $id => $object) {
      // Exclude properties starting with @.
      if (substr($id, 0, 1) == '@' || gettype($object) != 'object' || !isset($object->type)) {
        continue;
      }
      $type = $object->type;
      if ($type == 'string') {
        if (isset($object->title)) {
          $property_list[$parent . '.' . $id] = ucfirst($parent) . ': ' . "{$object->title} ({$id})";
        }
        else {
          $property_list[$parent . '.' . $id] = ucfirst($parent) . ': ' . ucfirst($id);
        }
      }
      // Find nested properties.
      elseif (in_array($type, ['array', 'object'])) {
        if (isset($object->properties) && gettype($object->properties == 'object')) {
          $property_list = $this->buildPropertyList($object->properties, $id, $property_list);
        }
        elseif (isset($object->items) && gettype($object->items) == 'object' && isset($object->items->properties)) {
          $property_list = $this->buildPropertyList($object->items->properties, $id, $property_list);
        }
      }
    }

    return $property_list;
  }

}
