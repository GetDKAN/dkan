<?php

namespace Dkan\Datastore\Storage\KeyValue;

use Dkan\Datastore\Storage\IKeyValue;

class Memory implements IKeyValue {
  private $storage = [];

  public function set($key, $value) {
    $this->storage[$key] = $value;
  }

  public function get($key, $default = NULL) {
    if (isset($this->storage[$key])) {
      return $this->storage[$key];
    }
    else {
      if ($default) {
        $this->set($key, $default);
        return $this->get($key);
      }
      else {
        return NULL;
      }
    }
  }

}