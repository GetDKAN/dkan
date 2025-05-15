<?php

namespace Drupal\harvest\ETL\Load;

use Drupal\harvest\Harvester;
use Drupal\harvest\Util;

/***
 * Abstract class for harvest loading.
 */
abstract class Load {

  /**
   * Harvest plan, decoded JSON object.
   *
   * @var object
   */
  protected $harvestPlan;

  /**
   * The hash storage object.
   *
   * @var object
   */
  protected $hashStorage;

  /**
   * The hash storage object.
   *
   * @var object
   */
  protected $itemStorage;

  /**
   * Save a harvested dataset item into our metastore.
   *
   * @param object $item
   *   An object representing the dataset. This object should comport to
   *   DCAT-US Schema v1.1 once JSON-encoded.
   *
   * @see schema/collections/dataset.json
   */
  abstract protected function saveItem(object $item);

  /**
   * Load constructor.
   *
   * @param object $harvest_plan
   *   The harvest plan.
   * @param object $hash_storage
   *   The hash storage.
   * @param object $item_storage
   *   The item storage.
   */
  public function __construct(
    object $harvest_plan,
    object $hash_storage,
    object $item_storage,
  ) {
    $this->harvestPlan = $harvest_plan;
    $this->hashStorage = $hash_storage;
    $this->itemStorage = $item_storage;
  }

  /**
   * Create and store the hash for the harvest item if appropriate.
   *
   * @param object $item
   *   The harvest item.
   *
   * @return int
   *   The status of the harvest item.
   *
   * @throws \JsonException
   */
  public function run(object $item): int {
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
   * @param object $item
   *   The item we're dealing with, as an arbitrary data structure.
   *
   * @return int
   *   One of the various Harvester constants.
   *
   * @see \Drupal\harvest\Harvester
   */
  protected function itemState(object $item): int {
    if (!isset($item->identifier)) {
      throw new \Exception('Item does not have an identifier ' . json_encode($item));
    }

    // Load the hash from storage, for comparison, if it exists.
    $hash = NULL;
    if ($hash_json = $this->hashStorage->retrieve(Util::getDatasetId($item))) {
      $hash_object = json_decode($hash_json);
      $hash = $hash_object->hash ?? NULL;
    }

    return match (TRUE) {
      // There was no existing hash in storage, so this is a new item.
      empty($hash) => Harvester::HARVEST_LOAD_NEW_ITEM,
      // Hash matches item's current or legacy hash, so no change.
      // Legacy hash might match if the hash was generated
      // before we changed the hashing system.
      $hash === Util::generateHash($item) || $hash === Util::legacyGenerateHash($item) => Harvester::HARVEST_LOAD_UNCHANGED,
      // We do have a past hash record, but neither new nor
      // legacy hash matched, so update the dataset.
      default => Harvester::HARVEST_LOAD_UPDATED_ITEM,
    };

  }

}
