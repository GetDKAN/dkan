<?php

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\LockableDrupalVariables;
use Dkan\Datastore\Resource;

/**
 * Class Factory.
 */
class Factory {

  private $resource;

  private $classes = [];

  public function __construct(Resource $resource) {
    $this->resource = $resource;
    $this->setupClassHierarchy();
  }

  public function setClass($class) {
    array_unshift($this->classes, $class);
  }

  public function get() {
    foreach ($this->classes as $class) {
      try {
        return $this->getManager($class);
      }
      catch (\Exception $e) {}
    }
    throw new \Exception("Datastore could not be loaded");

  }

  private function getManager($class) {
    $exists = class_exists($class);
    if (!$exists) {
      throw new \Exception("The class {$class} does not exist.");
    }

    $interfaces = class_implements($class);
    $interface = "Dkan\Datastore\Manager\ManagerInterface";
    if (!in_array($interface, $interfaces)) {
      throw new \Exception("The class {$class} does not implement the interface {$interface}.");
    }

    return new $class($this->resource);
  }

  private function setupClassHierarchy() {
    array_unshift($this->classes, $this->getClass());

    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state = $state_storage->get($this->resource->getId());

    if ($state) {
      $class = $state['class'];
      array_unshift($this->classes, $class);
    }
  }

  private function getClass() {
    $info = dkan_datastore_managers_info();

    if (empty($info)) {
      throw new \Exception("No Datastore Managers are active");
    }

    /* @var $i Info */
    foreach ($info as $i) {
      return $i->getClass();
    }
  }

}
