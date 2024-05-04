<?php

namespace Drupal\common\Contracts;

/**
 * Interface for conditional query.
 */
interface ConditionerInterface {

  /**
   * Retrieve only objects with properties of certain values.
   *
   * @param string $property
   *   Property to filter on.
   * @param string $value
   *   Property value to filter against.
   */
  public function conditionByIsEqualTo(string $property, string $value): void;

}
