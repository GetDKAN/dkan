<?php

namespace Drupal\dkan_datastore\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_datastore\Manager\Helper as DatastoreHelper;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\NodeInterface;
use Dkan\Datastore\Resource;

/**
 * Main services for the datastore.
 */
class Datastore {

  protected $entityTypeManager;
  protected $logger;
  protected $helper;

  /**
   * Constructor for datastore service.
   */
  public function __construct(
            EntityTypeManagerInterface $entityTypeManager,
            LoggerChannelInterface $logger,
            DatastoreHelper $helper
    ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger            = $logger;
    $this->helper            = $helper;
  }

  /**
   * Start import process for a resource, provided by UUID.
   *
   * @param string $uuid
   *   UUID for resource node.
   * @param bool $deferred
   *   Send to the queue for later? Will import immediately if FALSE.
   */
  public function import($uuid, $deferred = FALSE) {
    foreach ($this->getDistributionsFromUuid($uuid) as $distribution) {
      if (!empty($deferred)) {
        $this->queueImport($uuid, $this->getResource($distribution));
      }
      else {
        $this->processImport($distribution);
      }
    }
  }

  /**
   * Drop all datastores for a given node.
   *
   * @param string $uuid
   *   UUID for resource or dataset node. If dataset, will drop datastore for
   *   all connected resources.
   */
  public function drop($uuid) {

    foreach ($this->getDistributionsFromUuid($uuid) as $distribution) {
      $this->processDrop($distribution);
    }
  }

  /**
   * Queue a resource for import.
   *
   * @param string $uuid
   *   Resource node UUID.
   * @param Dkan\Datastore\Resource $resource
   *   Datastore resource object.
   */
  protected function queueImport($uuid, Resource $resource) {
    /** @var \Drupal\dkan_datastore\Manager\DeferredImportQueuer $deferredImporter */
    $deferredImporter = \Drupal::service('dkan_datastore.manager.deferred_import_queuer');
    $queueId          = $deferredImporter->createDeferredResourceImport($uuid, $resource);
    $this->logger->notice("New queue (ID:{$queueId}) was created for `{$uuid}`");
  }

  /**
   * Start a datastore import process for a distribution object.
   *
   * @param object $distribution
   *   Metadata distribution object decoded from JSON. Must have an $identifier.
   */
  protected function processImport($distribution) {
    $datastore = $this->getDatastore($this->getResource($distribution));
    $datastore->import();
  }

  /**
   * Drop a datastore for a given distribution object.
   *
   * @param object $distribution
   *   Metadata distribution object decoded from JSON. Must have an $identifier.
   */
  protected function processDrop($distribution) {
    $datastore = $this->getDatastore($this->getResource($distribution));
    $datastore->drop();
  }

  /**
   * Create a datastore Resource object from distribution metadata.
   *
   * @param object $distribution
   *   Metadata distribution object decoded from JSON. Must have an $identifier.
   *
   * @return Dkan\Datastore\Resource
   *   Resource object.
   */
  protected function getResource($distribution) {
    $distribution_node = $this->helper
      ->loadNodeByUuid($distribution->identifier);

    return $this->helper
      ->newResource($distribution_node->id(), $distribution->data->downloadURL);
  }

  /**
   * Build a datastore Manager from a resource object.
   *
   * @param Dkan\Datastore\Resource $resource
   *   Datastore resource object.
   *
   * @return Dkan\Datastore\Manager
   *   Datastore manager object.
   */
  protected function getDatastore(Resource $resource) {
    /* @var  $builder  Builder */
    $builder = \Drupal::service('dkan_datastore.manager.builder');
    $builder->setResource($resource);
    return $builder->build();
  }

  /**
   * Get one or more distributions (aka resources) from a uuid.
   *
   * @param string $uuid
   *   Dataset node UUID.
   *
   * @return object
   *   Distribution metadata object decoded from JSON.
   */
  protected function getDistributionsFromUuid($uuid) {

    $node = $this->helper
      ->loadNodeByUuid($uuid);

    if (!($node instanceof NodeInterface) || 'data' !== $node->getType()) {
      $this->logger->error("We were not able to load a data node with uuid {$uuid}.");
      return [];
    }
    // Verify data is of expected type.
    $expectedTypes = [
      'dataset',
      'distribution',
    ];
    if (!isset($node->field_data_type->value) || !in_array($node->field_data_type->value, $expectedTypes)) {
      $this->logger->error("Data not among expected types: " . implode(" ", $expectedTypes));
      return [];
    }
    // Standardize whether single resource object or several in a dataset.
    $metadata      = json_decode($node->field_json_metadata->value);
    $distributions = [];
    if ($node->field_data_type->value == 'dataset') {
      $distributions = $metadata->distribution;
    }
    if ($node->field_data_type->value == 'distribution') {
      $distributions[] = $metadata;
    }

    return $distributions;
  }

}
