<?php

namespace Drupal\common\Contracts;

/**
 * Interface for sorting records from a query.
 */
interface SorterInterface {

  /**
   * Sort records as ascending by the given property.
   *
   * @param string $property
   *   The property to sort on.
   */
  public function sortByAscending(string $property): void;

  /**
   * Sort records as descending by the given property.
   *
   * @param string $property
   *   The property to sort on.
   */
  public function sortByDescending(string $property): void;

}
