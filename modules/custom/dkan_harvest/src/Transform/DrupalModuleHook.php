<?php

namespace Drupal\dkan_harvest\Transform;

use Harvest\ETL\Transform\Transform;

/**
 * Class.
 */
class DrupalModuleHook extends Transform {

  /**
   * Harvest plan.
   *
   * @var string
   */
  protected $harvestPlan;

  /**
   * Public.
   */
  public function run($item) {
    return $this->hook($item);
  }

  /**
   * Hook.
   *
   * @codeCoverageIgnore
   */
  public function hook($item) {
    $module_handler = \Drupal::moduleHandler();
    $new_item = $module_handler
      ->invokeAll('dkan_harvest_transform', [$item, $this->harvestPlan]);

    return $new_item;
  }

}
