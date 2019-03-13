<?php

namespace Drupal\dkan_harvest\Transform;

use Harvest\Transform\Transform;

class DrupalModuleHook extends Transform {


  protected $harvestPlan;

  function __construct($harvest_plan) {
    parent::__construct($harvest_plan);
  }

  function run(&$items) {
    $this->hook($items);
  }

  function hook(&$items) {
    $module_handler = \Drupal::moduleHandler();
    $new_items = $module_handler
      ->invokeAll('dkan_harvest_transform',[$items, $this->harvestPlan]);

    if ($new_items && !empty($new_items)) {
      $items = $new_items;
    }
  }
}
