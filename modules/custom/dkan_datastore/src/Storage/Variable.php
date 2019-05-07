<?php

namespace Drupal\dkan_datastore\Storage;

use Dkan\Datastore\Storage\IKeyValue;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 *
 */
class Variable implements IKeyValue {

  protected $store = [];

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   *
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
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
    $all = $this->configFactory
      ->get('dkan_datastore.keyvalue')
      ->get('data');
    return unserialize($all);
  }

  /**
   *
   */
  protected function pushAll() {
    $serialized = serialize($this->store);
    $config = $this->configFactory
      ->getEditable('dkan_datastore.keyvalue');
    $config->set('data', $serialized)->save();
  }

}
