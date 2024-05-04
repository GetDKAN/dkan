<?php

namespace Drupal\common\Contracts;

/**
 * Interface for bulk retrieval of all records.
 */
interface BulkRetrieverInterface {

  /**
   * Retrieve all.
   *
   * @return array
   *   An array of ids.
   */
  public function retrieveAll(): array;

}
