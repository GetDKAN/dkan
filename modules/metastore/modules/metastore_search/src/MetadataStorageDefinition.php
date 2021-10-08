<?php

namespace Drupal\metastore_search;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;

/**
 * Metadata Storage Definition.
 */
class MetadataStorageDefinition implements MetadataStorageDefinitionInterface {

  /**
   * Metadata schema.
   *
   * @var object
   */
  protected $schema;

  /**
   * Construct a MetadataStorageDefinition object.
   */
  public function __construct(string $data_type) {
    $this->schema = json_decode(\Drupal::service('dkan.metastore.schema_retriever')->retrieve($data_type));
  }

  /**
   * {@inheritDoc}
   */
  public function getPropertyNames(): array {
    return array_keys((array) $this->schema->properties);
  }

  /**
   * {@inheritDoc}
   */
  public function getPropertyDefinitions(): array {
    $definitions = [];

    foreach ($this->getPropertyNames() as $property) {
      $defs = $this->getPropertyDefinition($property);
      $definitions = array_merge($definitions, $defs);
    }

    return $definitions;
  }

  /**
   * {@inheritDoc}
   */
  protected function getPropertyDefinition(string $property): array {
    $defs = [];
    $type = $this->schema->properties->{$property}->type ?? "string";
    if (($type == "array" && isset($this->schema->properties->{$property}->items->properties))
    || $type == "object") {
      $defs = $this->getComplexPropertyDefinition($this->schema->properties->{$property}, $type, $property);
    }
    else {
      $defs[$property] = $this->getDefinitionObject($type);
    }
    return $defs;
  }

  /**
   * Private.
   */
  protected function getComplexPropertyDefinition($property_items, $type, $property): array {
    $prefix = '';
    $definitions = [];
    $child_properties = [];
    if ($type == "array" && isset($property_items->items->properties)) {
      $prefix = $property . '__item__';
      $props = $property_items->items->properties;
      $child_properties = array_keys((array) $props);
    }
    elseif ($type == "object" && isset($property_items->properties)) {
      $prefix = $property . '__';
      $props = $property_items->properties;
      $child_properties = array_keys((array) $props);
    }
    else {
      $definitions[$property] = $this->getDefinitionObject($type);
    }

    foreach ($child_properties as $child) {
      $definitions[$prefix . $child] = $this->getDefinitionObject($type);
    }
    return $definitions;
  }

  /**
   * Private.
   */
  protected function getDefinitionObject($type) {
    if ($type == "object" || $type == "any") {
      $type = "string";
    }

    if ($type == "array") {
      return ListDataDefinition::create("string");
    }
    return DataDefinition::createFromDataType($type);
  }

}
