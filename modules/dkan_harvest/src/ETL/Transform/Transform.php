<?php

namespace Drupal\harvest\ETL\Transform;

/**
 * Abstract class for performing harvest transformations.
 */
abstract class Transform {

  /**
   * Harvest plan, string-encoded JSON.
   *
   * @var object
   */
  protected $harvestPlan;

  /**
   * Transform constructor.
   *
   * @param object|string $harvest_plan
   *   The harvest plan.
   */
  public function __construct(object|string $harvest_plan) {
    $this->harvestPlan = $harvest_plan;
  }

  /**
   * Run the transformation.
   *
   * @param object $item
   *   The item to transform.
   *
   * @return mixed
   *   The results of the action.
   */
  abstract public function run(object $item);

}
