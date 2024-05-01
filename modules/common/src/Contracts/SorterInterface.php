<?php

namespace Drupal\common\Contracts;

interface SorterInterface {

  public function sortByAscending(string $property);

  public function sortByDescending(string $property);

}
