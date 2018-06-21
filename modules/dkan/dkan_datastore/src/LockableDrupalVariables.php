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
   * Return bin.
   *
   * Sets the whole bin and all of its variables.
   * Releases the lock set if borrowBin() was called.
   *
   * @param array $bin
   *   the full bin.
   */
  public function returnBin($bin) {
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
    $counter = 0;
    do {
      if ($counter >= 1) {
        sleep(1);
      }
      $success = @mkdir('/tmp/dkan.lock', 0700);
      $counter++;
    } while (!$success);
  }

  /**
   * Private method.
   */
  private function releaseLock() {
    rmdir('/tmp/dkan.lock');
  }

}
