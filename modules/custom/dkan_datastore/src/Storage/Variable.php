<?php

namespace Drupal\dkan_datastore\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Variable storage.
 */
class Variable {

  protected $store = [];

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor function.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    $store = $this->getAll();
    if ($store) {
      $this->store = $store;
    }
  }

  /**
   * Set a variable key in the store to a value.
   */
  public function set($key, $value) {
    $this->store[$key] = $value;
    $this->pushAll();
  }

  /**
   * Get a variable value from the store.
   */
  public function get($key, $default = NULL) {
    return isset($this->store[$key]) ? $this->store[$key] : $default;
  }

  /**
   * Get full variable array from store.
   */
  protected function getAll() {
    $all = $this->configFactory
      ->get('dkan_datastore.keyvalue')
      ->get('data');
    return unserialize($all);
  }

  /**
   * Push values to variable store.
   */
  protected function pushAll() {
    $serialized = serialize($this->store);
    $config = $this->configFactory
      ->getEditable('dkan_datastore.keyvalue');
    $config->set('data', $serialized)->save();
  }

}
