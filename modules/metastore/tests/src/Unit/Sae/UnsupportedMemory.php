<?php

namespace Drupal\Tests\metastore\Unit\Sae;

use Drupal\metastore\Storage\MetastoreStorageInterface;

class UnsupportedMemory implements MetastoreStorageInterface {
  private $storage = [];

  public function retrieve(string $id) {
    if (isset($this->storage[$id])) {
      return $this->storage[$id];
    }
    return NULL;
  }

  public function store($data, string $id = NULL): string {
    if (!isset($this->storage[$id])) {
      $this->storage[$id] = $data;
      return $id;
    }
    $this->storage[$id] = $data;
    return $id;
  }

  public function remove(string $id) {
    if (isset($this->storage[$id])) {
      unset($this->storage[$id]);
      return TRUE;
    }
    return FALSE;
  }

  public function retrieveAll() : array {}

  public function retrievePublished(string $uuid) : ?string {}

  public function publish(string $uuid) : string {}

}
