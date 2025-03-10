<?php

namespace Drupal\harvest\ETL\Load;

use Drupal\harvest\Harvester;
use Drupal\harvest\Util;

abstract class Load {

  protected $harvestPlan;
  protected $hashStorage;
  protected $itemStorage;

  abstract protected function saveItem($item);

  public function __construct(
    $harvest_plan,
    $hash_storage,
    $item_storage,
  ) {
    $this->harvestPlan = $harvest_plan;
    $this->hashStorage = $hash_storage;
    $this->itemStorage = $item_storage;
  }

  public function run($item): int {
    $state = $this->itemState($item);

    if ($state == Harvester::HARVEST_LOAD_NEW_ITEM || $state == Harvester::HARVEST_LOAD_UPDATED_ITEM) {
      $this->saveItem($item);

      $identifier = Util::getDatasetId($item);

      $hash = Util::generateHash($item);
      $object = (object) [
        'harvest_plan_id' => $this->harvestPlan->identifier,
        'hash' => $hash,
      ];
      $this->hashStorage->store(json_encode($object), $identifier);
    }

    return $state;
  }

  /**
   * Determine what to do next for the item, based on hash comparison.
   *
   * @param $item
   *   The item we're dealing with, as an arbitrary data structure.
   *
   * @return int
   *   One of the various Harvester constants.
   *
   * @see \Drupal\harvest\Harvester
   */
  protected function itemState($item): int {
    if (isset($item->identifier)) {
      // Load the hash from storage, for comparison, if it exists.
      $hash = NULL;
      if ($hash_json = $this->hashStorage->retrieve(Util::getDatasetId($item))) {
        $hash_object = json_decode($hash_json);
        $hash = $hash_object->hash ?? NULL;
      }

      if ($hash) {
        if ($hash === Util::generateHash($item)) {
          // Hashes matched, so no change.
          return Harvester::HARVEST_LOAD_UNCHANGED;
        }
        else {
          // Hashes don't match, so try again with the legacy hash
          // generator. This might match if the hash was generated
          // before we changed the hashing system.
          if ($hash === Util::legacyGenerateHash($item)) {
            return Harvester::HARVEST_LOAD_UNCHANGED;
          }
          // We do have a past hash record, but neither new nor
          // legacy hash matched, so update the dataset.
          return Harvester::HARVEST_LOAD_UPDATED_ITEM;
        }
      }
      // There was no existing hash in storage, so this is a new
      // item.
      return Harvester::HARVEST_LOAD_NEW_ITEM;
    }
    throw new \Exception('Item does not have an identifier ' . json_encode($item));
  }

}
