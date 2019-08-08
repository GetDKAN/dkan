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

  /**
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   *
   * @var \Drupal\dkan_datastore\Manager\Helper
   */
  protected $helper;

  /**
   * Public.
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
   * Public.
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
   * Public.
   */
  public function drop($uuid) {

    foreach ($this->getDistributionsFromUuid($uuid) as $distribution) {
      $this->processDrop($distribution);
    }
  }

  /**
   *
   */
  protected function queueImport($uuid, $resource) {
    /** @var \Drupal\dkan_datastore\Manager\DeferredImportQueuer $deferredImporter */
    $deferredImporter = \Drupal::service('dkan_datastore.manager.deferred_import_queuer');
    $queueId          = $deferredImporter->createDeferredResourceImport($uuid, $resource);
    $this->logger->notice("New queue (ID:{$queueId}) was created for `{$uuid}`");
  }

  /**
   *
   */
  protected function processImport($distribution) {
    $datastore = $this->getDatastore($this->getResource($distribution));
    $datastore->import();
  }

  /**
   *
   */
  protected function processDrop($distribution) {
    $datastore = $this->getDatastore($this->getResource($distribution));
    $datastore->drop();
  }

  /**
   *
   */
  protected function getResource($distribution) {
    $distribution_node = $this->helper
      ->loadNodeByUuid($distribution->identifier);

    return $this->helper
      ->newResource($distribution_node->id(), $distribution->data->downloadURL);
  }

  /**
   *
   */
  protected function getDatastore($resource) {
    /** @var  $builder  Builder */
    $builder = \Drupal::service('dkan_datastore.manager.builder');
    $builder->setResource($resource);
    return $builder->build();
  }

  /**
   * Get one or more distributions (aka resources) from a uuid.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $nodeStorage
   * @param string $uuid
   *
   * @return array
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
