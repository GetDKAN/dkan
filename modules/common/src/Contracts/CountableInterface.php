<?php

namespace Drupal\common\Contracts;

/**
 * Interface for countable objects.
 *
 * @todo Replace with \Countable?
 */
interface CountableInterface {

  /**
   * Count elements of an object.
   *
   * @return int
   *   Number of elements represented by this object.
   */
  public function count(): int;

}
