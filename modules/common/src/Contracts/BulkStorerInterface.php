<?php

namespace Drupal\common\Contracts;

/**
 * Interface for storage of multiple records.
 */
interface BulkStorerInterface {

  /**
   * Store multiple.
   *
   * @param array $data
   *   An array of strings to be stored.
   */
  public function storeMultiple(array $data);

}
