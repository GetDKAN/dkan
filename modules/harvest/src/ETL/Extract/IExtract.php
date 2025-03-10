<?php

namespace Drupal\harvest\ETL\Extract;

interface IExtract {

  /**
   * Run the extraction.
   *
   * @return array
   *   An array of php objects.
   */
  public function run(): array;

}
