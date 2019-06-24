<?php

namespace Drupal\dkan_harvest\Transform;

use Harvest\ETL\Transform\Transform;

/**
 *
 */
class DrupalModuleHook extends Transform {

  protected $harvestPlan;

  /**
   *
   */
  public function __construct($harvest_plan) {
    parent::__construct($harvest_plan);
  }

  /**
   *
   */
  public function run($item) {
    return $this->hook($item);
  }

  /**
   * @codeCoverageIgnore
   */
  public function hook($item) {
    $module_handler = \Drupal::moduleHandler();
    $new_item = $module_handler
      ->invokeAll('dkan_harvest_transform', [$item, $this->harvestPlan]);

    return $new_item;
  }

}
