<?php

namespace Dkan\Datastore;

/**
 * Class Locker.
 */
class Locker {

  /**
   * Public method.
   */
  public function getLock() {
    $delay = variable_get('dkan_datastore_lock_delay', 5);
    $timeout = variable_get('dkan_datastore_lock_timeout', 60);
    $name = 'dkan_datastore_lock';

    $lock = lock_acquire($name, $timeout)
    || (
      !lock_wait($name, $delay)
      && lock_acquire($name, $timeout)
    );
    if (!$lock) {
      watchdog('dkan_datastore_lock_timeout', "Failed to get datastore variable lock, wait delay exceeded.", array(),WATCHDOG_CRITICAL);
      throw new \Exception("Failed to get datastore variable lock, wait delay exceeded.");
    }
  }

  /**
   * Public method.
   */
  public function releaseLock() {
    $name = 'dkan_datastore_lock';
    lock_release($name);
  }

}

