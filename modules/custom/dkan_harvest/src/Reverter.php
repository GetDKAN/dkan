<?php

namespace Drupal\dkan_harvest;

use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Drupal\dkan_harvest\Log\MakeItLog;
use Drupal\dkan_harvest\Storage\Hash;
use Harvest\Storage\Storage;

class Reverter {
  use MakeItLog;

  public $sourceId;
  private $hashStorage;

  function __construct($sourceId, Storage $hash_storage) {
    $this->sourceId = $sourceId;
    $this->hashStorage = $hash_storage;
  }

  function run() {
    $this->log('DEBUG', 'revert', 'Reverting harvest ' . $this->sourceId);

    $uuids = array_keys($this->hashStorage->retrieveAll());

    $datastore_storage = new DrupalNodeDataset();

    $counter = 0;
    foreach ($uuids as $uuid) {
      $datastore_storage->remove($uuid);
      $this->hashStorage->remove($uuid);
      $counter++;
    }
    return $counter;
  }

}
