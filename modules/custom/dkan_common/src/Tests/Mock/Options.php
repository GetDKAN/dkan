<?php

namespace Drupal\dkan_common\Tests\Mock;

/**
 * Options class.
 */
class Options {

  private $options;
  private $storeId;

  /**
   * Options constructor.
   */
  public function __construct() {
    $this->options = [];
    $this->storeId = NULL;
  }

  /**
   * Set use.
   */
  public function use($storeId) {
    $this->storeId = $storeId;
    return $this;
  }

  /**
   * Get use.
   */
  public function getUse() {
    return $this->storeId;
  }

  /**
   * Add a new option and its return.
   */
  public function add($option, $return) {
    $this->options[$option] = $return;
    return $this;
  }

  /**
   * Options.
   */
  public function options() {
    return array_keys($this->options);
  }

  /**
   * Return.
   */
  public function return($option) {
    $return = $this->options[$option];

    if ($return instanceof Sequence) {
      $return = $return->return();
    }

    return $return;
  }

}
