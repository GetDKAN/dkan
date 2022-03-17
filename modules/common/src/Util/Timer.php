<?php

namespace Drupal\common\Util;

/**
 * Timer utility object.
 */
class Timer {

  /**
   * Start times in microseconds.
   *
   * @var float[]
   */
  private $starts = [];

  /**
   * End times in microseconds.
   *
   * @var float[]
   */
  private $ends = [];

  /**
   * Start.
   */
  public function start($id, $iteration = 0) {
    $this->starts[$id][$iteration] = microtime(TRUE);
  }

  /**
   * End.
   */
  public function end($id, $iteration = 0) {
    $this->ends[$id][$iteration] = microtime(TRUE);
  }

  /**
   * Average.
   */
  public function average($id) {
    $total = 0;
    $counter = 0;
    foreach ($this->ends[$id] as $iteration => $end) {
      $start = $this->starts[$id][$iteration];
      $total += ($end - $start);
      $counter++;
    }
    return $total / $counter;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $strings = [];
    foreach ($this->ends as $id => $data) {
      $strings[] = "{$id} AVG: {$this->average($id)}";
    }
    return implode(PHP_EOL, $strings) . PHP_EOL;
  }

}
