<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Queue\QueueFactory;

/**
 * Checks for orphanned references in deleted datasets.
 */
class OrphanChecker {
  use HelperTrait;

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueService;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $configService, QueueFactory $queueService) {
    $this->queueService = $queueService;
    $this->setConfigService($configService);
  }

  /**
   * Check for orphan references when a dataset is being deleted.
   *
   * This function queues each property that holds a reference to be put through
   * the orphan-check process.
   *
   * If the object referenced is still referenced by other objects it is
   * left alone. If no other reference exists, it is deleted.
   *
   * When an object is deleted, the referenced objects for all properties must
   * be checked.
   *
   * @param object $data
   *   Dataset to be deleted.
   */
  public function processReferencesInDeletedDataset($data) {
    if (!is_object($data)) {
      throw new \Exception("data must be an object.");
    }
    // Cycle through the dataset properties we seek to reference.
    foreach ($this->getPropertyList() as $property_id) {
      if (isset($data->{$property_id})) {
        $this->processReferencesInDeletedProperty($property_id, $data->{$property_id});
      }
    }
  }

  /**
   * Public.
   *
   * @param object $old_dataset
   *   Old dataset.
   * @param object $new_dataset
   *   Updated dataset.
   */
  public function processReferencesInUpdatedDataset($old_dataset, $new_dataset) {
    $this->objectsCheck([$old_dataset, $new_dataset]);
    // Cycle through the dataset properties being referenced, check for orphans.
    foreach ($this->getPropertyList() as $property_id) {
      if (!isset($old_dataset->{$property_id})) {
        // The old dataset had no value for this property, thus no references
        // could be deleted. Safe to skip checking for orphan reference.
        continue;
      }
      $oldProperty = $old_dataset->{$property_id};
      $newProperty = (!isset($new_dataset->{$property_id})) ?
        $this->emptyPropertyOfSameType($oldProperty) :
        $new_dataset->{$property_id};

      $this->processReferencesInUpdatedProperty($property_id, $oldProperty, $newProperty);
    }
  }

  /**
   * Private.
   *
   * @param string $property_id
   *   The dataset property.
   * @param array|string $uuids
   *   The uuids to process.
   */
  private function processReferencesInDeletedProperty($property_id, $uuids) {
    // Treat single uuid as an array of one uuid.
    if (!is_array($uuids)) {
      $uuids = [$uuids];
    }
    foreach ($uuids as $uuid) {
      $this->queueReferenceForRemoval($property_id, $uuid);
    }
  }

  /**
   * Private.
   *
   * @param string $property_id
   *   The dataset property.
   * @param string $uuid
   *   The uuid to queue for removal.
   *
   * @codeCoverageIgnore
   */
  private function queueReferenceForRemoval($property_id, $uuid) {
    $this->queueService->get('orphan_reference_processor')
      ->createItem([
        $property_id,
        $uuid,
      ]);
  }

  /**
   * Private.
   *
   * @param string $property_id
   *   The dataset property.
   * @param mixed $old_value
   *   The old value to be replaced.
   * @param mixed $new_value
   *   The new value to replaced it with.
   */
  private function processReferencesInUpdatedProperty($property_id, $old_value, $new_value) {
    if (!is_array($old_value)) {
      $old_value = [$old_value];
      $new_value = [$new_value];
    }

    foreach (array_diff($old_value, $new_value) as $removed_reference) {
      $this->queueReferenceForRemoval($property_id, $removed_reference);
    }
  }

  /**
   * Private.
   */
  private function objectsCheck($objects) {
    foreach ($objects as $object) {
      if (!is_object($object)) {
        throw new \Exception("data given must be an object.");
      }
    }
  }

}
