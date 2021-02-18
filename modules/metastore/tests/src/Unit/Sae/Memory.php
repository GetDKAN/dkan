<?php

namespace Drupal\Tests\metastore\Unit\Sae;

use Contracts\BulkRetrieverInterface;

class Memory extends UnsupportedMemory implements BulkRetrieverInterface {

  private $storage = [];

  public function retrieveAll(): array {
    return $this->storage;
  }

}
