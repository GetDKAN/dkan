<?php

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\LockableDrupalVariables;
use Dkan\Datastore\Resource;

/**
 * Class Factory.
 *
 * Builds a datastore manager for a resource.
 */
class Factory {

  private $resource;

  private $classes = [];

  /**
   * Constructor.
   */
  public function __construct(Resource $resource) {
    $this->resource = $resource;
    $this->setupClassHierarchy();
  }

  /**
   * Set a class.
   *
   * This class will be given priority when trying to
   * create a datastore manager for the given resource.
   */
  public function setClass($class) {
    array_unshift($this->classes, $class);
  }

  /**
   * Get the datastore manager.
   */
  public function get() {
    foreach ($this->classes as $class) {
      try {
        return $this->getManager($class);
      }
      catch (\Exception $e) {
      }
    }
    throw new \Exception("Datastore could not be loaded");

  }

  /**
   * Get manager.
   */
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

  /**
   * Create an array of datastore manager classes.
   *
   * It picks a "random" manager class, and, if available,
   * the class from the datastore state.
   *
   * This is useful as a hierarcy to eliminate failures
   * if our datastore state becomes corrupt, and the official
   * manager is no longer valid or available.
   */
  private function setupClassHierarchy() {
    array_unshift($this->classes, $this->getClass());

    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state = $state_storage->get($this->resource->getId());

    if ($state) {
      $class = $state['class'];
      array_unshift($this->classes, $class);
    }
  }

  /**
   * Choose the first class from the manager's info array.
   */
  private function getClass() {
    $info = dkan_datastore_managers_info();

    if (empty($info)) {
      throw new \Exception("No Datastore Managers are active");
    }

    /* @var $i \Dkan\Datastore\Manager\Info */
    $i = array_shift($info);
    return $i->getClass();
  }

}
