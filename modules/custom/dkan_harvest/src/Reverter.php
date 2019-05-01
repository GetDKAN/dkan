<?php

namespace Drupal\dkan_harvest;

use Drupal\dkan_harvest\Log\MakeItLog;
use Harvest\Storage\Storage;

/**
 * Reverter.
 */
class Reverter {
  use MakeItLog;

  public $sourceId;
  protected $hashStorage;

  /**
   * Reverter.
   *
   * @param mixed $sourceId
   *   Source Id.
   * @param \Harvest\Storage\Storage $hash_storage
   *   Storage.
   */
  public function __construct($sourceId, Storage $hash_storage) {
    $this->sourceId = $sourceId;
    $this->hashStorage = $hash_storage;
  }

  /**
   * Run the revert.
   *
   * @return int
   *   Count of rows preverted.
   */
  public function run() {
    $this->log('DEBUG', 'revert', 'Reverting harvest ' . $this->sourceId);

    $uuids = array_keys($this->hashStorage->retrieveAll());

    /** @var \Drupal\dkan_api\Storage\DrupalNodeDataset $datastore_storage */
    // Cannot use DI here since this class is called by a non drupal package.
    $datastore_storage = \Drupal::service('dkan_api.storage.drupal_node_dataset');

    $counter = 0;
    foreach ($uuids as $uuid) {
      $datastore_storage->remove($uuid);
      $this->hashStorage->remove($uuid);
      $counter++;
    }
    return $counter;
  }

}
