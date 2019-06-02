<?php

namespace Drupal\Dkan\Datastore\Storage;

use Dkan\Datastore\Storage\IKeyValue;

/**
 *
 */
class Variable implements IKeyValue {

  protected $store = [];

  /**
   *
   */
  public function __construct() {
    $store = $this->getAll();
    if ($store) {
      $this->store = $store;
    }
  }

  /**
   *
   */
  public function set($key, $value) {
    $this->store[$key] = $value;
    $this->pushAll();
  }

  /**
   *
   */
  public function get($key, $default = NULL) {
    return isset($this->store[$key]) ? $this->store[$key] : $default;
  }

  /**
   *
   */
  protected function getAll() {
    $all = variable_get("dkan_datastore.keyvalue", serialize([]));
    return unserialize($all);
  }

  /**
   *
   */
  protected function pushAll() {
    $serialized = serialize($this->store);
    variable_set("dkan_datastore.keyvalue", $serialized);
  }

}
