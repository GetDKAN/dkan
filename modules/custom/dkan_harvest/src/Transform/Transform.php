<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Log\MakeItLog;

class Transform {
  use MakeItLog;

  protected $harvestPlan;

  function __construct($harvest_plan) {
    $this->harvestPlan = $harvest_plan;
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
