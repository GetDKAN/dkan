<?php

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\LockableDrupalVariables;
use Dkan\Datastore\Resource;

/**
 * Class Factory.
 */
class Factory {

  /**
   * Create a Datastore Manager.
   *
   * @param Resource $resource
   *   A resource object.
   * @param string $class
   *   The Datastore Manager class.
   *
   * @throws \Exception
   *   When an incorrect class is passed.
   */
  public static function create(Resource $resource, $class = NULL) {

    if (!$class) {
      $state_storage = new LockableDrupalVariables("dkan_datastore");
      $state = $state_storage->get($resource->getId());

      if ($state) {
        $class = $state['class'];
      }
      else {
        return NULL;
      }
    }

    $exists = class_exists($class);
    if ($exists) {
      $interfaces = class_implements($class);
      $interface = "Dkan\Datastore\Manager\ManagerInterface";
      if (in_array($interface, $interfaces)) {
        return new $class($resource);
      }
      else {
        throw new \Exception("The class {$class} does not implement the interface {$interface}.");
      }
    }
    else {
      throw new \Exception("The class {$class} does not exist.");
    }
  }

}
