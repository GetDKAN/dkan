<?php

namespace Drupal\dkan_common\Tests\Mock;

/**
 * Sequence class.
 */
class Sequence {

  private $sequence = [];
  private $counter = 0;

  /**
   * Add possible return.
   */
  public function add($return) {
    $this->sequence[] = $return;

    return $this;
  }

  /**
   * Get the next return.
   */
  public function return() {
    $index = $this->counter;

    // Always return the last element when done.
    if (!isset($this->sequence[$index])) {
      $index = count($this->sequence) - 1;
    }

    $return = $this->sequence[$index];
    $this->counter++;
    return $return;
  }

}
