<?php

namespace Drupal\Tests\metastore\Unit\Sae;

use Contracts\IdGeneratorInterface;

class Sequential implements IdGeneratorInterface {
  private $id = 0;

  public function generate() {
    return ++$this->id;
  }

}
