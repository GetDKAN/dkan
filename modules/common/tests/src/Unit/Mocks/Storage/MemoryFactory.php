<?php

namespace Drupal\Tests\common\Unit\Mocks\Storage;

use Contracts\FactoryInterface;

class MemoryFactory implements FactoryInterface {

  private ?array $stores = NULL;

  public function getInstance(string $identifier, array $config = []) {
    if (!isset($this->stores[$identifier])) {
      $this->stores[$identifier] = new Memory();
    }
    return $this->stores[$identifier];
  }

}
