<?php

namespace Drupal\harvest\Storage;

use Contracts\IdGeneratorInterface;

/**
 * Extracts identifiers from metastore data objects.
 *
 * @codeCoverageIgnore
 */
class IdGenerator implements IdGeneratorInterface {

  /**
   * Data.
   *
   * @var mixed
   */
  protected $data;

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
