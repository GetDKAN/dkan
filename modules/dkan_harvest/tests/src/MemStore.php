<?php

namespace Drupal\Tests\dkan_harvest;

use Contracts\Mock\Storage\Memory;
use Drupal\dkan_harvest\Storage\StorageInterface;

class MemStore extends Memory implements StorageInterface {

  public function retrieveAll(): array {
    return array_keys(parent::retrieveAll());
  }

}
