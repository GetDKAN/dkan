<?php

namespace Drupal\dkan_search\ComplexData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\ItemList;
use Drupal\Core\TypedData\TypedData;
use Drupal\dkan_search\Facade\ComplexDataFacade;

/**
 * Dataset.
 */
class Dataset extends ComplexDataFacade {
  private $data;

  /**
   * Definition.
   */
  public static function definition() {
    $definitions = [];

    /* @var  $schemaRetriever  SchemaRetriever */
    $schemaRetriever = \Drupal::service("dkan_schema.schema_retriever");
    $json = $schemaRetriever->retrieve("dataset");
    $object = json_decode($json);
    $properties = array_keys((array) $object->properties);

    foreach ($properties as $property) {
      $type = $object->properties->{$property}->type;
      $definitions[$property] = self::getDefinition($type);
    }

    return $definitions;
  }

  /**
   * Private.
   */
  private static function getDefinition($type) {
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
   * @inheritDoc
   */
  public function get($property_name) {
    $definitions = self::definition();

    if (!isset($definitions[$property_name])) {
      return NULL;
    }

    $definition = $definitions[$property_name];

    if ($definition instanceof ListDataDefinition) {
      $property = new ItemList($definition, $property_name);
      $values = $this->data->{$property_name};
      if (is_string($values)) {
        $values = json_decode($values);
      }
      $property->setValue($values);
    }
    else {
      $property = new class($definition, $property_name) extends TypedData {};
      $property->setValue($this->data->{$property_name});
    }

    return $property;
  }

  /**
   * Inherited.
   *
   * @inheritDoc
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
   * @inheritDoc
   */
  public function getValue() {
    return $this->data;
  }

}
