<?php

namespace Drupal\dkan_data\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use stdClass;

/**
 * Referencer.
 */
class Referencer {
  use HelperTrait;

  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configService, EntityTypeManager $entityTypeManager) {
    $this->setConfigService($configService);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Replaces some dataset property values with references.
   *
   * @param object $data
   *   Dataset json object.
   *
   * @return object
   *   Json object modified with references to some of its properties' values.
   */
  public function reference(stdClass $data) {
    // Cycle through the dataset properties we seek to reference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $data->{$property_id} = $this->referenceProperty($property_id, $data->{$property_id});
      }
    }
    return $data;
  }

  /**
   * References a dataset property's value, general case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param mixed $data
   *   Single value or array of values to be referenced.
   *
   * @return string|array
   *   Single reference, or an array of references.
   */
  private function referenceProperty(string $property_id, $data) {
    if (is_array($data)) {
      return $this->referenceMultiple($property_id, $data);
    }
    else {
      // Case for $data being an object or a string.
      return $this->referenceSingle($property_id, $data);
    }
  }

  /**
   * References a dataset property's value, array case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param array $values
   *   The array of values to be referenced.
   *
   * @return array
   *   The array of uuid references.
   */
  private function referenceMultiple(string $property_id, array $values) : array {
    $result = [];
    foreach ($values as $value) {
      $data = $this->referenceSingle($property_id, $value);
      if (NULL !== $data) {
        $result[] = $data;
      }
    }
    return $result;
  }

  /**
   * References a dataset property's value, string or object case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|object $value
   *   The value to be referenced.
   *
   * @return string
   *   The Uuid reference, or unchanged value.
   */
  private function referenceSingle(string $property_id, $value) {
    $uuid = $this->checkExistingReference($property_id, $value);
    if (!$uuid) {
      $uuid = $this->createPropertyReference($property_id, $value);
    }
    if ($uuid) {
      return $uuid;
    }
    else {
      if ($this->loggerService) {
        $this->loggerService->get('value_referencer')->error(
          'Neither found an existing nor could create a new reference for property_id: @property_id with value: @value',
          [
            '@property_id' => $property_id,
            '@value' => var_export($value, TRUE),
          ]
        );
      }
      return NULL;
    }
  }

  /**
   * Checks for an existing value reference for that property id.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|object $data
   *   The property's value used to find an existing reference.
   *
   * @return string|null
   *   The existing reference's uuid, or null if not found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function checkExistingReference(string $property_id, $data) {
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'field_data_type' => $property_id,
        'title' => md5(json_encode($data)),
      ]);

    if ($node = reset($nodes)) {
      return $node->uuid();
    }
    return NULL;
  }

  /**
   * Creates a new value reference for that property id in a data node.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|object $value
   *   The property's value.
   *
   * @return string|null
   *   The new reference's uuid, or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createPropertyReference(string $property_id, $value) {
    // Create json metadata for the reference.
    $data = new stdClass();
    $data->identifier = $this->getUuidService()->generate($property_id, $value);
    $data->data = $value;

    // Create node to store this reference.
    $node = $this->entityTypeManager
      ->getStorage('node')
      ->create([
        'title' => md5(json_encode($value)),
        'type' => 'data',
        'uuid' => $data->identifier,
        'field_data_type' => $property_id,
        'field_json_metadata' => json_encode($data),
      ]);
    $node->save();

    return $node->uuid();
  }

}
