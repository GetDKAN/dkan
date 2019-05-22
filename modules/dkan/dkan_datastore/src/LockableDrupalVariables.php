<?php

namespace Dkan\Datastore;

/**
 * Class LockableDrupalVariables.
 */
class LockableDrupalVariables {

  private $binName;

  /**
   * LockableDrupalVariables constructor.
   */
  public function __construct($bin_name) {
    $this->binName = $bin_name;
  }

  /**
   * Get all the variables in this bin.
   *
   * This operation will lock other bin operataions until the
   * return bin method is called.
   *
   * @return array
   *   All of the variables in this bin.
   */
  public function borrowBin() {
    $this->getLock();

    $bin = variable_get($this->binName, []);

    return $bin;
  }

  /**
   * Get bin.
   */
  public function getBin() {
    $bin = variable_get($this->binName, []);
    return $bin;
  }

  /**
   * Return bin.
   *
   * Sets the whole bin and all of its variables.
   * Releases the lock set if borrowBin() was called.
   *
   * @param array $bin
   *   The full bin.
   */
  public function returnBin(array $bin) {
    variable_set($this->binName, $bin);

    $this->releaseLock();
  }

  /**
   * Set a variable.
   *
   * @param string $id
   *   The variable's id.
   * @param mixed $data
   *   The variable's value.
   */
  public function set($id, $data) {
    $this->getLock();

    $bin = variable_get($this->binName, []);
    $bin[$id] = $data;
    variable_set($this->binName, $bin);

    $this->releaseLock();
  }

  /**
   * Get the variable with the given id.
   *
   * @param string $id
   *   The variable's id.
   *
   * @return mixed
   *   The variable's value.
   */
  public function get($id) {
    $this->getLock();

    $bin = variable_get($this->binName, []);

    $this->releaseLock();

    return isset($bin[$id]) ? $bin[$id] : NULL;
  }

  /**
   * Delete the variable with the given id.
   *
   * @param string $id
   *   The id of the variable.
   */
  public function delete($id) {
    $this->getLock();

    $bin = variable_get($this->binName, []);
    unset($bin[$id]);
    variable_set($this->binName, $bin);

    $this->releaseLock();
  }

  /**
   * Private method.
   */
  private function getLock() {
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
      throw new Exception("Failed to get datastore variable lock, wait delay exceeded.");
    }
  }

  /**
   * Private method.
   */
  private function releaseLock() {
    $name = 'dkan_datastore_lock';
    lock_release($name);
  }

}
