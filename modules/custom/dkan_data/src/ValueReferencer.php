<?php

declare(strict_types = 1);

namespace Drupal\dkan_data;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_data\Service\Uuid5;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Logger\LoggerChannelFactory;
use stdClass;

/**
 * Replaces some dataset property values with references, or vice versa.
 *
 * @package Drupal\dkan_api\Storage
 */
class ValueReferencer {

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
  const DEREFERENCE_OUTPUT_REFERENCE_IDS = 2;

  /**
   * Store the dereferencing method for current request.
   *
   * @var int
   */
  protected $dereferenceMethod;

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
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerService;

  /**
   * ValueReferencer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Injected entity type manager.
   * @param \Drupal\dkan_data\Service\Uuid5 $uuidService
   *   Injected uuid service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   Injected config service.
   * @param \Drupal\Core\Queue\QueueFactory $queueService
   *   Injected queue service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerService
   *   Injected logger factory service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Uuid5 $uuidService, ConfigFactoryInterface $configService, QueueFactory $queueService, LoggerChannelFactory $loggerService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->uuidService = $uuidService;
    $this->configService = $configService;
    $this->queueService = $queueService;
    $this->loggerService = $loggerService;
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
  protected function referenceProperty(string $property_id, $data) {
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
  protected function referenceMultiple(string $property_id, array $values) : array {
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
  protected function referenceSingle(string $property_id, $value) {
    $uuid = $this->checkExistingReference($property_id, $value);
    if (!$uuid) {
      $uuid = $this->createPropertyReference($property_id, $value);
    }
    if ($uuid) {
      return $uuid;
    }
    else {
      $this->loggerService->get('value_referencer')->error(
        'Neither found an existing nor could create a new reference for property_id: @property_id with value: @value',
        [
          '@property_id' => $property_id,
          '@value' => var_export($value, TRUE),
        ]
      );
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
  protected function checkExistingReference(string $property_id, $data) {
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
  protected function createPropertyReference(string $property_id, $value) {
    // Create json metadata for the reference.
    $data = new stdClass();
    $data->identifier = $this->uuidService->generate($property_id, $value);
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
   * Setter for dereferencing method.
   *
   * @param int $method
   *   Method.
   *
   * @return int
   *   Int.
   */
  protected function setDereferenceMethod(int $method) {
    return $this->dereferenceMethod = $method;
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
  protected function dereferenceProperty(string $property_id, $uuid) {
    if (is_array($uuid)) {
      return $this->dereferenceMultiple($property_id, $uuid);
    }
    elseif (is_string($uuid) && $this->uuidService->isValid($uuid)) {
      return $this->dereferenceSingle($property_id, $uuid);
    }
    else {
      $this->loggerService->get('value_referencer')->error(
        'Unexpected data type when dereferencing property_id: @property_id with uuid: @uuid',
        [
          '@property_id' => $property_id,
          '@uuid' => var_export($uuid, TRUE),
        ]
      );
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
  protected function dereferenceMultiple(string $property_id, array $uuids) : array {
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
        if ($this->dereferenceMethod == self::DEREFERENCE_OUTPUT_REFERENCE_IDS) {
          return $metadata;
        }
        else {
          return $metadata->data;
        }
      }
    }
    // If a property node was not found, it most likely means it was deleted
    // while still being referenced.
    $this->loggerService->get('value_referencer')->error(
      'Property @property_id reference @uuid not found',
      [
        '@property_id' => $property_id,
        '@uuid' => var_export($uuid, TRUE),
      ]
    );
    return NULL;
  }

  /**
   * Check for orphan references when a dataset is being deleted.
   *
   * @param object $data
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
   * Private.
   *
   * @param string $property_id
   *   The dataset property.
   * @param array|string $uuids
   *   The uuids to process.
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
   * Private.
   *
   * @param string $property_id
   *   The dataset property.
   * @param string $uuid
   *   The uuid to queue for removal.
   *
   * @codeCoverageIgnore
   */
  protected function queueReferenceForRemoval($property_id, $uuid) {
    $this->queueService->get('orphan_reference_processor')
      ->createItem([
        $property_id,
        $uuid,
      ]);
  }

  /**
   * Public.
   *
   * @param object $old_dataset
   *   Old dataset.
   * @param object $new_dataset
   *   Updated dataset.
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
   * Private.
   *
   * @param string $property_id
   *   The dataset property.
   * @param mixed $old_value
   *   The old value to be replaced.
   * @param mixed $new_value
   *   The new value to replaced it with.
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
   * Private.
   *
   * @param mixed $data
   *   Data whose type we want to match.
   *
   * @return array|string
   *   Either the empty string or an empty array.
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
