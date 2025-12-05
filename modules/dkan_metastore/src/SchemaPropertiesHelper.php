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
    $schema_object = json_decode((string) $schema);

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
    $schema_object = json_decode((string) $schema);

    return $this->buildPropertyList($schema_object->properties);
  }

  /**
   * Build a list of JSON schema properties.
   *
   * @param object $input
   *   JSON Schema object we're parsing.
   * @param string $parent
   *   Parent object.
   * @param array $property_list
   *   Array we're building of schema properties.
   *
   * @return array
   *   List of schema properties' title and description.
   *
   * @see https://json-schema.org/understanding-json-schema/reference/object.html#properties
   */
  private function buildPropertyList($input, string $parent = 'dataset', array &$property_list = []): array {
    foreach ($input as $name => $property) {
      $this->parseProperty($name, $property, $parent, $property_list);
    }
    return $property_list;
  }

  /**
   * Parse a single property from a JSON schema.
   *
   * @param string $name
   *   Property name.
   * @param mixed $property
   *   JSON schema "property" object.
   * @param string $parent
   *   The parent JSON Schema propety of the current property.
   * @param array $property_list
   *   Array we're building of schema properties.
   */
  private function parseProperty(string $name, mixed $property, string $parent, array &$property_list) {
    // Exclude properties starting with @ or that are not proper objects.
    if (str_starts_with($name, '@') || gettype($property) != 'object' || !isset($property->type)) {
      return;
    }

    // Strings can be added directly to the list.
    if ($property->type == 'string') {
      $title = isset($property->title) ? $property->title . ' (' . $name . ')' : ucfirst($name);
      $property_list[$parent . '_' . $name] = ucfirst($parent) . ': ' . $title;
    }
    // Non-strings (arrays and objects) can be parsed for nested properties.
    else {
      $this->parseNestedProperties($name, $property, $property_list);
    }
  }

  /**
   * Parse nested schema properties.
   *
   * @param string $name
   *   Property ID.
   * @param object $property
   *   JSON Schema "property" object we're parsing.
   * @param array $property_list
   *   Array we're building of schema properties.
   */
  private function parseNestedProperties(string $name, $property, array &$property_list = []) {
    if (isset($property->properties) && gettype($property->properties == 'object')) {
      $property_list = $this->buildPropertyList($property->properties, $name, $property_list);
    }
    elseif (isset($property->items) && gettype($property->items) == 'object' && isset($property->items->properties)) {
      $property_list = $this->buildPropertyList($property->items->properties, $name, $property_list);
    }
  }

}
