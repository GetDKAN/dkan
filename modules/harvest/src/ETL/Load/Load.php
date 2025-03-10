<?php

namespace Drupal\harvest\ETL\Load;

use Drupal\harvest\Harvester;
use Drupal\harvest\Util;

/***
 * Abstract class for harvest loading.
 */
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
    if (!isset($item->identifier)) {
      throw new \Exception('Item does not have an identifier ' . json_encode($item));
    }

    // Load the hash from storage, for comparison, if it exists.
    $hash = NULL;
    if ($hash_json = $this->hashStorage->retrieve(Util::getDatasetId($item))) {
      $hash_object = json_decode($hash_json);
      $hash = $hash_object->hash ?? NULL;
    }

    if (empty($hash)) {
      // There was no existing hash in storage, so this is a new item.
      return Harvester::HARVEST_LOAD_NEW_ITEM;
    }

    if ($hash === Util::generateHash($item) || $hash === Util::legacyGenerateHash($item)) {
      // Hash matches item's current or legacy hash, so no change.
      // Legacy hash might match if the hash was generated
      // before we changed the hashing system.
      return Harvester::HARVEST_LOAD_UNCHANGED;
    }
    else {
      // We do have a past hash record, but neither new nor
      // legacy hash matched, so update the dataset.
      return Harvester::HARVEST_LOAD_UPDATED_ITEM;
    }
  }

}
