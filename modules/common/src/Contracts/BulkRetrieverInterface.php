<?php

namespace Drupal\common\Contracts;

interface BulkRetrieverInterface {

  /**
   * Retrieve all.
   *
   * @return array
   *   An array of ids.
   */
  public function retrieveAll(): array;

}
