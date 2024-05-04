<?php

namespace Drupal\common\Contracts;

/**
 * Interface to add a limit to queries.
 */
interface LimiterInterface {

  /**
   * Limit the number of records returned on a query.
   *
   * @param int $number_of_items
   *   The number of items to limit to.
   */
  public function limitTo(int $number_of_items): void;

}
