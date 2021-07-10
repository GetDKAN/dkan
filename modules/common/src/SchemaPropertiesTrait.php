<?php

namespace Drupal\common;

/**
 * Trait SchemaPropertiesTrait.
 *
 * @package Drupal\common
 */
trait SchemaPropertiesTrait {

  /**
   * Retrieve schema properties.
   *
   * @return array
   *   List of schema properties' title and description.
   */
  public function retrieveSchemaProperties(): array {
    // Create a json object from our schema.
    $schemaRetriever = \Drupal::service('dkan.metastore.schema_retriever');
    $schema = $schemaRetriever->retrieve('dataset');
    $schema_object = json_decode($schema);

    // Build a list of the schema properties' title and description.
    $property_list = [];
    foreach ($schema_object->properties as $property_id => $property_object) {
      if (isset($property_object->title)) {
        $property_list[$property_id] = "{$property_object->title} ({$property_id})";
      } else {
        $property_list[$property_id] = ucfirst($property_id);
      }
    }

    return $property_list;
  }

}
