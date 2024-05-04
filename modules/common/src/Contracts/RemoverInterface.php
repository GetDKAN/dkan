<?php

namespace Drupal\common\Contracts;

/**
 * Interface to remove a record.
 */
interface RemoverInterface {

  /**
   * Remove a record.
   *
   * @param string $id
   *   The identifier for the data.
   *
   * @return mixed
   *   Identifier of the removed record.
   */
  public function remove(string $id);

}
