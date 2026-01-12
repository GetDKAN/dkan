<?php

namespace Drupal\harvest\Storage;

use Contracts\IdGeneratorInterface;

/**
 * Extracts identifiers from metastore data objects.
 *
 * @codeCoverageIgnore
 *
 * @deprecated Is this dead code?
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
    $this->data = json_decode((string) $json);
  }

  /**
   * Public.
   */
  public function generate() {
    return $this->data->identifier ?? NULL;
  }

}
