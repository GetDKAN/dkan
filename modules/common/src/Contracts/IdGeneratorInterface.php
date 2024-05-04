<?php

namespace Drupal\common\Contracts;

/**
 * Interface for generating an identifier.
 */
interface IdGeneratorInterface {

  /**
   * Generate or glean an identifier.
   *
   * @return mixed
   *   An identifier.
   */
  public function generate();

}
