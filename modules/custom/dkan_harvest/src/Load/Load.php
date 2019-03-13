<?php

namespace Drupal\dkan_harvest\Load;

use Drupal\dkan_harvest\Log\MakeItLog;
use Drupal\dkan_harvest\Storage\Hash;

abstract class Load {
  use MakeItLog;

  const NEW_ITEM = 0;
  const UPDATED_ITEM = 1;
  const SAME_ITEM = 2;

  protected $harvestPlan;
  private $results = [
    'created' => 0,
    'updated' => 0,
    'skipped' => 0
  ];

  abstract protected function saveItem($item);

  function __construct($harvest_plan) {
    $this->harvestPlan = $harvest_plan;
  }

  public function run($items) {

    foreach ($items as $item) {
      try {
        if ($this->itemState($item) == self::NEW_ITEM || $this->itemState($item) == self::UPDATED_ITEM) {
          $this->saveItem($item);

          $hash_store = new Hash();
          $identifier = $item->identifier;

          if ($this->itemState($item) == self::NEW_ITEM) {
            $hash_store->create($identifier, $this->harvestPlan->sourceId, $hash_store->hashGenerate($item));
            $this->results['created']++;
          }
          else {
            $hash_store->update($identifier, $hash_store->hashGenerate($item));
            $this->results['updated']++;
          }
        }
      }
      catch (\Exception $e) {
        $this->log("ERROR", "Harvest:Load:SaveItem", $e->getMessage());
        $this->results['skipped']++;
      }
    }

    return $this->results;
  }

  private function itemState($item) {
    if (isset($item->identifier)) {
      $hash_store = new Hash();
      $identifier = $item->identifier;
      $hash = $hash_store->read($identifier);

      if ($hash) {
        $new_hash = $hash_store->hashGenerate($item);
        if ($hash == $new_hash) {
          return self::SAME_ITEM;
        }
        else {
          return self::UPDATED_ITEM;
        }
      }
      else {
        return self::NEW_ITEM;
      }
    }
    else {
      throw new \Exception("Item does not have an identifier " . json_encode($item));
    }

  }

}
