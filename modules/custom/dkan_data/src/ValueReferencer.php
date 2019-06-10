<?php

declare(strict_types = 1);

namespace Drupal\dkan_data;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Queue\QueueFactory;
use stdClass;

/**
 * Replaces some dataset property values with references, or vice versa.
 *
 * @package Drupal\dkan_api\Storage
 */
class ValueReferencer {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The uuid service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configService;

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueService;

  /**
   * ValueReferencer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Injected entity type manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidService
   *   Injected uuid service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   Injected config service.
   * @param \Drupal\Core\Queue\QueueFactory $queueService
   *   Injected queue service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UuidInterface $uuidService, ConfigFactoryInterface $configService, QueueFactory $queueService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->uuidService = $uuidService;
    $this->configService = $configService;
    $this->queueService = $queueService;
  }

  /**
   * Replaces some dataset property values with references.
   *
   * @param \stdClass $data
   *   Dataset json object.
   *
   * @return \stdClass
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
  protected function referenceProperty(string $property_id, $data) {
    if (is_array($data)) {
      return $this->referenceMultiple($property_id, $data);
    }
    else {
      // Object or string.
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
  protected function referenceMultiple(string $property_id, array $values) : array {
    $result = [];
    foreach ($values as $value) {
      $result[] = $this->referenceSingle($property_id, $value);
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
  protected function referenceSingle(string $property_id, $value) {
    $uuid = $this->checkExistingReference($property_id, $value);
    if (!$uuid) {
      $uuid = $this->createPropertyReference($property_id, $value);
    }
    if ($uuid) {
      return $uuid;
    }
    else {
      // In the unlikely case we neither found an existing reference nor could
      // create a new reference, return the unchanged value.
      return $value;
    }
  }

  /**
   * Checks for an existing value reference for that property id.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param $data
   *   The property's value used to find an existing reference.
   *
   * @return string|null
   *   The existing reference's uuid, or null if not found.
   */
  protected function checkExistingReference(string $property_id, $data) {
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'field_data_type' => $property_id,
        'title' => md5(json_encode($data)),
      ]);

    if ($node = reset($nodes)) {
      return $node->uuid->value;
    }
    return NULL;
  }

  /**
   * Creates a new value reference for that property id in a data node.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param mixed $value
   *   The property's value.
   *
   * @return string|null
   *   The new reference's uuid, or null.
   */
  protected function createPropertyReference(string $property_id, $value) {
    // Create json metadata for the reference.
    $data = new stdClass();
    $data->identifier = $this->uuidService->generate();
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

  /**
   * Replaces value references in a dataset with with their actual values.
   *
   * @param \stdClass $data
   *   The json metadata object.
   *
   * @return mixed
   *   Modified json metadata object.
   */
  public function dereference(stdClass $data) {
    // Cycle through the dataset properties we seek to dereference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $data->{$property_id} = $this->dereferenceProperty($property_id, $data->{$property_id});
      }
    }
    return $data;
  }

  /**
   * Replaces a property reference with its actual value, general case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string|array $uuids
   *   A single reference uuid string, or an array reference uuids.
   *
   * @return string|array
   *   An array of dereferenced values, or a single one.
   */
  protected function dereferenceProperty(string $property_id, $data) {
    if (is_array($data)) {
      return $this->dereferenceMultiple($property_id, $data);
    }
    else {
      return $this->dereferenceSingle($property_id, $data);
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
  protected function dereferenceMultiple(string $property_id, array $uuids) : array {
    $result = [];
    foreach ($uuids as $uuid) {
      $result[] = $this->dereferenceSingle($property_id, $uuid);
    }
    return $result;
  }

  /**
   * Replaces a property reference with its actual value, string or object case.
   *
   * @param string $property_id
   *   The dataset property id.
   * @param string $str
   *   Either a uuid or an actual json value.
   *
   * @return string
   *   The data from this reference.
   */
  protected function dereferenceSingle(string $property_id, string $uuid) {
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'field_data_type' => $property_id,
        'uuid' => $uuid,
      ]);
    if ($node = reset($nodes)) {
      if (isset($node->field_json_metadata->value)) {
        $metadata = json_decode($node->field_json_metadata->value);
        return $metadata->data;
      }
    }
    // If str was not found, it's unlikely it was a uuid to begin with. It was
    // most likely never referenced to begin with, so return unchanged.
    return $uuid;
  }

  /**
   * Check for orphan references when a dataset is being deleted.
   *
   * @param \stdClass $data
   *   Dataset to be deleted.
   */
  public function processReferencesInDeletedDataset(stdClass $data) {
    // Cycle through the dataset properties we seek to reference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $this->processReferencesInDeletedProperty($property_id, $data->{$property_id});
      }
    }
  }

  /**
   *
   */
  protected function processReferencesInDeletedProperty($property_id, $uuids) {
    // Treat single uuid as an array of one uuid.
    if (!is_array($uuids)) {
      $uuids = [$uuids];
    }
    foreach ($uuids as $uuid) {
      $this->queueReferenceForRemoval($property_id, $uuid);
    }
  }

  /**
   * @param $property_id
   * @param $uuid
   *
   * @codeCoverageIgnore since no logic, single call to queue worker.
   */
  protected function queueReferenceForRemoval($property_id, $uuid) {
    $this->queueService->get('orphan_reference_processor')
      ->createItem([
        $property_id,
        $uuid,
      ]);
  }

  /**
   *
   */
  public function processReferencesInUpdatedDataset(stdClass $old_dataset, stdClass $new_dataset) {
    // Cycle through the dataset properties being referenced, check for orphans.
    foreach ($this->getPropertyList() as $property_id) {
      if (!isset($old_dataset->{$property_id})) {
        // The old dataset had no value for this property, thus no references
        // could be deleted. Safe to skip checking for orphan reference.
        continue;
      }
      if (!isset($new_dataset->{$property_id})) {
        $new_dataset->{$property_id} = $this->emptyPropertyOfSameType($old_dataset->{$property_id});
      }
      $this->processReferencesInUpdatedProperty($property_id, $old_dataset->{$property_id}, $new_dataset->{$property_id});
    }
  }

  /**
   *
   */
  protected function processReferencesInUpdatedProperty($property_id, $old_value, $new_value) {
    if (!is_array($old_value)) {
      $old_value = [$old_value];
      $new_value = [$new_value];
    }
    foreach (array_diff($old_value, $new_value) as $removed_reference) {
      $this->queueReferenceForRemoval($property_id, $removed_reference);
    }
  }

  /**
   * @param $data
   *
   * @return array|string
   */
  protected function emptyPropertyOfSameType($data) {
    if (is_array($data)) {
      return [];
    }
    return "";
  }

  /**
   * Get the list of dataset properties being referenced.
   *
   * @return array
   *   List of dataset properties.
   *
   * @Todo: consolidate with dkan_api RouteProvider's getPropertyList.
   */
  protected function getPropertyList() : array {
    $list = $this->configService->get('dkan_data.settings')->get('property_list');
    return array_values(array_filter($list));
  }

}
