<?php
namespace Drupal\dkan_datastore\Storage;

use Dkan\Datastore\Storage\IKeyValue;

class Variable implements IKeyValue {

  private $store = [];

  public function __construct() {
    $store = $this->getAll();
    if ($store) {
      $this->store = $store;
    }
  }

  public function set($key, $value) {
    $this->store[$key] = $value;
    $this->pushAll();
  }

  public function get($key, $default = NULL) {
    return isset($this->store[$key])? $this->store[$key] : $default;
  }

  private function getAll() {
    $config = \Drupal::config('dkan_datastore.keyvalue');
    $all = $config->get('data');
    return unserialize($all);
  }

  private function pushAll() {
    $serialized = serialize($this->store);
    $config = \Drupal::service('config.factory')->getEditable('dkan_datastore.keyvalue');
    $config->set('data', $serialized)->save();
  }
}