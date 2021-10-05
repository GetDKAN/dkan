<?php

namespace Drupal\metastore_search\ComplexData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\ListDataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\ItemList;
use Drupal\Core\TypedData\TypedData;

use Drupal\metastore_search\Facade\ComplexDataFacade;

/**
 * Dataset.
 */
class DkanMetadataFacade extends ComplexDataFacade {
  protected $data;
  protected $dataType;

  /**
   * Constructor.
   */
  public function __construct(MetadataStorageDefinitionInterface $storage_definition, string $json) {
    $this->storageDefinition = $storage_definition;
    $this->data = json_decode($json);
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function get($property_name) {
    $definitions = $this->storageDefinition->getPropertyDefinitions();

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
      $property = new class ($definition, $property_name) extends TypedData{};
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

    if (preg_match('/(.*)__(.*)/', $property_name, $matches)) {
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
    if (preg_match('/(.*)__item__(.*)/', $property_name, $matches)
      && isset($this->data->{$matches[1]})
      && is_array($this->data->{$matches[1]})) {
      foreach ($this->data->{$matches[1]} as $dist) {
        $values[] = isset($dist->{$matches[2]}) ? $dist->{$matches[2]} : [];
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
    $properties = [];

    foreach ($this->storageDefinition->getPropertyNames() as $propertyName) {
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
