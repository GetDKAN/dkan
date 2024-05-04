<?php

namespace Drupal\common\Contracts;

/**
 * Interface to offset query results.
 */
interface OffsetterInterface {

  /**
   * Set the number of records to offset by.
   *
   * @param int $offset
   *   The number of records to offset by.
   */
  public function offsetBy(int $offset): void;

}
