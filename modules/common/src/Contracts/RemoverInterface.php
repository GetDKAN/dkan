<?php

namespace Drupal\common\Contracts;

interface RemoverInterface {

  /**
   * Remove.
   *
   * @param string $id
   *   The identifier for the data.
   */
  public function remove(string $id);

}
