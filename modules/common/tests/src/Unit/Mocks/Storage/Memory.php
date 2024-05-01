<?php

namespace Drupal\Tests\common\Unit\Mocks\Storage;

use Drupal\common\Contracts\BulkRetrieverInterface;
use Drupal\common\Contracts\RetrieverInterface;
use Drupal\common\Contracts\StorerInterface;

class Memory implements RetrieverInterface, StorerInterface, BulkRetrieverInterface {

  protected $storage = [];

  public function retrieve(string $id) {
    if (isset($this->storage[$id])) {
      return $this->storage[$id];
    }
    return NULL;
  }

  public function retrieveAll(): array {
    return $this->storage;
  }

  public function store($data, string $id = NULL): string {
    if (!isset($id)) {
      throw new \Exception('An id is required to store the data.');
    }
    if (!isset($this->storage[$id])) {
      $this->storage[$id] = $data;
      return $id;
    }
    $this->storage[$id] = $data;
    return TRUE;
  }

  public function remove(string $id) {
    if (isset($this->storage[$id])) {
      unset($this->storage[$id]);
      return TRUE;
    }
    return FALSE;
  }

}
