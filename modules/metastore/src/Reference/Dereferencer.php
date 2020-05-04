<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\common\LoggerTrait;
use stdClass;

/**
 * Dereferencer.
 */
class Dereferencer {
  use HelperTrait;
  use LoggerTrait;

  /**
   * Indicates that dereferencing outputs data, the default case.
   *
   * @var int
   */
  const DEREFERENCE_OUTPUT_DEFAULT = 0;

  /**
   * Indicates that dereferencing outputs both the data and its uuid.
   *
   * @var int
   */
  const DEREFERENCE_OUTPUT_REFERENCE_IDS = 1;

  /**
   * Store the dereferencing method for current request.
   *
   * @var int
   */
  private $dereferenceMethod;

  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configService, EntityTypeManager $entityTypeManager) {
    $this->setConfigService($configService);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Replaces value references in a dataset with with their actual values.
   *
   * @param object $data
   *   The json metadata object.
   * @param int $method
   *   Represents the dereferencing method, data, identifier or both.
   *
   * @return mixed
   *   Modified json metadata object.
   */
  public function dereference(stdClass $data, int $method = self::DEREFERENCE_OUTPUT_DEFAULT) {

    $this->setDereferenceMethod($method);
    // Cycle through the dataset properties we seek to dereference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $data->{$property_id} = $this->dereferenceProperty($property_id, $data->{$property_id});
      }
    }
    return $data;
  }

  /**
   * Setter for dereferencing method.
   *
   * @param int $method
   *   Method.
   *
   * @return int
   *   Int.
   */
  private function setDereferenceMethod(int $method) {
    return $this->dereferenceMethod = $method;
  }

  /**
   * Replaces a property reference with its actual value, general case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|array $uuid
   *   A single reference uuid string, or an array of reference uuids.
   *
   * @return mixed
   *   An array of dereferenced values, a single one, or NULL.
   */
  private function dereferenceProperty(string $property_id, $uuid) {
    if (is_array($uuid)) {
      return $this->dereferenceMultiple($property_id, $uuid);
    }
    elseif (is_string($uuid) && $this->getUuidService()->isValid($uuid)) {
      return $this->dereferenceSingle($property_id, $uuid);
    }
    else {
      $this->log('value_referencer', 'Unexpected data type when dereferencing property_id: @property_id with uuid: @uuid',
        [
          '@property_id' => $property_id,
          '@uuid' => var_export($uuid, TRUE),
        ]);
      return NULL;
    }
  }

  /**
   * Replaces a property reference with its actual value, array case.
   *
   * @param string $property_id
   *   A dataset property id.
   * @param array $uuids
   *   An array of reference uuids.
   *
   * @return array
   *   An array of dereferenced values.
   */
  private function dereferenceMultiple(string $property_id, array $uuids) : array {
    $result = [];
    foreach ($uuids as $uuid) {
      $data = $this->dereferenceSingle($property_id, $uuid);
      if (NULL !== $data) {
        $result[] = $data;
      }
    }
    return $result;
  }

  /**
   * Replaces a property reference with its actual value, string or object case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string $uuid
   *   Either a uuid or an actual json value.
   *
   * @return object|string
   *   The data from this reference.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function dereferenceSingle(string $property_id, string $uuid) {
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'field_data_type' => $property_id,
        'uuid' => $uuid,
      ]);

    if ($node = reset($nodes)) {
      if (isset($node->field_json_metadata->value)) {
        $metadata = json_decode($node->field_json_metadata->value);
        return ($this->dereferenceMethod == self::DEREFERENCE_OUTPUT_REFERENCE_IDS) ? $metadata : $metadata->data;
      }
    }
    // If a property node was not found, it most likely means it was deleted
    // while still being referenced.
    $this->log(
      'value_referencer',
      'Property @property_id reference @uuid not found',
      [
        '@property_id' => $property_id,
        '@uuid' => var_export($uuid, TRUE),
      ]
    );

    return NULL;
  }

}
