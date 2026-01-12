<?php

namespace Drupal\Tests\harvest;

use Contracts\Mock\Storage\Memory;
use Drupal\harvest\Storage\StorageInterface;

class MemStore extends Memory implements StorageInterface {

  public function retrieveAll(): array {
    return array_keys(parent::retrieveAll());
  }

}
