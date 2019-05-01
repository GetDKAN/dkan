<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Log\MakeItLog;

/**
 *
 */
class Transform {
  use MakeItLog;

  protected $harvestPlan;

  /**
   *
   */
  public function __construct($harvest_plan) {
    $this->harvestPlan = $harvest_plan;
  }

  /**
   *
   */
  public function run(&$items) {
    $this->hook($items);
  }

  /**
   *
   */
  public function hook(&$items) {
    $module_handler = \Drupal::moduleHandler();
    $new_items = $module_handler
      ->invokeAll('dkan_harvest_transform', [$items, $this->harvestPlan]);

    if ($new_items && !empty($new_items)) {
      $items = $new_items;
    }
  }

}
