<?php

namespace Drupal\metastore\Reference;

use Contracts\FactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\common\LoggerTrait;

/**
 * Metastore dereferencer.
 */
class Dereferencer {
  use HelperTrait;
  use LoggerTrait;

  /**
   * Storage factory interface service.
   *
   * @var \Contracts\FactoryInterface
   */
  private $storageFactory;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configService, FactoryInterface $storageFactory) {
    $this->setConfigService($configService);
    $this->storageFactory = $storageFactory;
  }

  /**
   * Replaces value references in a dataset with with their actual values.
   *
   * @param object $data
   *   The json metadata object.
   *
   * @return mixed
   *   Modified json metadata object.
   */
  public function dereference($data) {
    if (!is_object($data)) {
      throw new \Exception("data must be an object.");
    }
    // Cycle through the dataset properties we seek to dereference.
    $ref = NULL;
    $actual = NULL;
    foreach ($this->getPropertyList() as $propertyId) {
      if (isset($data->{$propertyId})) {
        $referenceProperty = "%Ref:{$propertyId}";
        [$ref, $actual] = $this->dereferenceProperty($propertyId, $data->{$propertyId});
        $data->{$referenceProperty} = $ref;
        $data->{$propertyId} = $actual;
      }
    }
    return $data;
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
    $reference = [];
    $ref = NULL;
    $actual = NULL;
    foreach ($uuids as $uuid) {
      [$ref, $actual] = $this->dereferenceSingle($property_id, $uuid);
      if (NULL !== $ref && NULL !== $actual) {
        $result[] = $actual;
        $reference[] = $ref;
      }
    }
    return [$reference, $result];
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
    $storage = $this->storageFactory->getInstance($property_id);
    $value = $storage->retrieve($uuid);

    if ($value) {
      $metadata = json_decode($value);
      return [$metadata, $metadata->data];
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

    return [NULL, NULL];
  }

}
