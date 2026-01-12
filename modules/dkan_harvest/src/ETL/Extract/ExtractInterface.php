<?php

namespace Drupal\dkan_harvest\ETL\Extract;

/**
 * Harvest extraction interface.
 */
interface ExtractInterface {

  /**
   * Run the extraction.
   *
   * @return array
   *   An array of php objects.
   */
  public function run(): array;

}
