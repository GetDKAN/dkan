<?php

namespace Drupal\metastore_search\ComplexData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\ItemList;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\metastore_search\Facade\ComplexDataFacade;

/**
 * Dataset facade for metastore search API facet.
 */
class Dataset extends ComplexDataFacade {

  /**
   * Complex data object.
   *
   * @var object
   */
  private $data;

  /**
   * Definition.
   */
  public static function definition() {
    $definitions = [];

    /** @var   \Drupal\metastore\SchemaRetriever $schemaRetriever */
    $schemaRetriever = \Drupal::service("dkan.metastore.schema_retriever");
    $json = $schemaRetriever->retrieve("dataset");
    $object = json_decode((string) $json);
    $properties = array_keys((array) $object->properties);

    foreach ($properties as $property) {
      $type = $object->properties->{$property}->type ?? "string";
      $defs = self::getPropertyDefinition($type, $object, $property);
      $definitions = array_merge($definitions, $defs);
    }

    return $definitions;
  }

  /**
   * Private.
   */
  private static function getPropertyDefinition($type, $object, $property_name) {
    $defs = [];
    if (($type == "array" && isset($object->properties->{$property_name}->items->properties))
    || $type == "object") {
      $defs = self::getComplexPropertyDefinition($object->properties->{$property_name}, $type, $property_name);
    }
    else {
      $defs[$property_name] = self::getDefinitionObject($type);
      $defs[$property_name]->setLabel($object->properties->{$property_name}->title ?? $property_name);
      $defs[$property_name]->setDescription($object->properties->{$property_name}->description ?? '');
    }
    return $defs;
  }

  /**
   * Private.
   */
  private static function getComplexPropertyDefinition($property_items, $type, $property_name) {
    $prefix = '';
    $definitions = [];
    $child_properties = [];
    if ($type == "array" && isset($property_items->items->properties)) {
      $prefix = $property_name . '__item__';
      $props = $property_items->items->properties;
      $child_properties = array_keys((array) $props);
    }
    elseif ($type == "object" && isset($property_items->properties)) {
      $prefix = $property_name . '__';
      $props = $property_items->properties;
      $child_properties = array_keys((array) $props);
    }
    else {
      $definitions[$property_name] = self::getDefinitionObject($type);
    }

    foreach ($child_properties as $child) {
      $definitions[$prefix . $child] = self::getDefinitionObject($type);
      $definitions[$prefix . $child]->setLabel($props->{$child}->title ?? $property_name);
      $definitions[$prefix . $child]->setDescription($props->{$child}->description ?? '');
    }
    return $definitions;
  }

  /**
   * Private.
   */
  private static function getDefinitionObject($type) {
    if ($type == "object" || $type == "any") {
      $type = "string";
    }

    if ($type == "array") {
      return ListDataDefinition::create("string");
    }
    return DataDefinition::createFromDataType($type);
  }

  /**
   * Constructor.
   */
  public function __construct(string $json) {
    $this->data = json_decode($json);
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function get($property_name) {
    $definitions = self::definition();

    if (!isset($definitions[$property_name])) {
      return NULL;
    }

    $definition = $definitions[$property_name];

    if ($definition instanceof ListDataDefinition) {
      $property = new ItemList($definition, $property_name);
      $values = $this->getArrayValues($property_name);
      $property->setValue($values);
    }
    else {
      $property = new StringData($definition, $property_name);
      $value = $this->getPropertyValue($property_name);
      $property->setValue($value);
    }

    return $property;
  }

  /**
   * Private.
   */
  private function getPropertyValue($property_name) {
    $value = [];
    $matches = [];

    if (preg_match('/(.*)__(.*)/', (string) $property_name, $matches)) {
      // Check if property corresponds to an object.
      if (isset($matches[1])
      && isset($this->data->{$matches[1]})
      && isset($matches[2])
      && isset($this->data->{$matches[1]}->{$matches[2]})) {
        $value = $this->data->{$matches[1]}->{$matches[2]};
      }
    }
    elseif (isset($this->data->{$property_name})) {
      $value = $this->data->{$property_name};
    }

    return $value;
  }

  /**
   * Private.
   */
  private function getArrayValues($property_name) {
    $values = [];
    $matches = [];
    if (preg_match('/(.*)__item__(.*)/', (string) $property_name, $matches)
      && isset($this->data->{$matches[1]})
      && is_array($this->data->{$matches[1]})) {
      foreach ($this->data->{$matches[1]} as $dist) {
        $values[] = $dist->{$matches[2]} ?? [];
      }
    }
    elseif (isset($this->data->{$property_name})) {
      $values = $this->data->{$property_name};
      $values = is_string($values) ? json_decode($values) : $values;
    }

    return $values;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getProperties($include_computed = FALSE) {
    $definitions = self::definition();
    $properties = [];
    foreach (array_keys($definitions) as $propertyName) {
      $properties[$propertyName] = $this->get($propertyName);
    }
    return $properties;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getValue() {
    return $this->data;
  }

}
