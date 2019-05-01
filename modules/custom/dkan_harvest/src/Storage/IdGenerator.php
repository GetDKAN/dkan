<?php

namespace Drupal\dkan_harvest\Storage;

use Contracts\IdGenerator as ContractsIdGenerator;

/**
 *
 */
class IdGenerator implements ContractsIdGenerator {

  protected  $data;

  /**
   *
   */
  public function __construct($json) {
    $this->data = json_decode($json);
  }

  /**
   *
   */
  public function generate() {
    return isset($this->data->identifier) ? $this->data->identifier : NULL;
  }

}
