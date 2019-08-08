<?php

namespace Drupal\dkan_harvest\Storage;

use Contracts\IdGenerator as ContractsIdGenerator;

/**
 * Class.
 */
class IdGenerator implements ContractsIdGenerator {

  /**
   * Data.
   *
   * @var mixed
   */
  protected  $data;

  /**
   * Public.
   */
  public function __construct($json) {
    $this->data = json_decode($json);
  }

  /**
   * Public.
   */
  public function generate() {
    return isset($this->data->identifier) ? $this->data->identifier : NULL;
  }

}
