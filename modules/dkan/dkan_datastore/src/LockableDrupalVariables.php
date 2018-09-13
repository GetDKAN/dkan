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
    $counter = 0;
    $success = 0;
    do {
      if ($counter >= 1) {
        sleep(1);
      }

      // We have to query the variable table directly instead of
      // using variable_get, b/c this is a global lock that can/will
      // be set or released in different processes. variable_get
      // simply check a global variable set earlier in the request.
      // This global variable does not get updated during the
      // request even if another process changes the value in
      // the database.
      $query = db_select("variable", 'v');
      $query->fields('v', ['value']);
      $query->condition('name', "dkan_datastore_lock");
      $results = $query->execute();

      $exist = FALSE;
      foreach ($results as $result) {
        $exist = TRUE;
        $value = unserialize($result->value);
        break;
      }

      if (!$exist || $value == 0) {
        variable_set('dkan_datastore_lock', 1);
        $success = 1;
      }

      $counter++;
    } while (!$success);
  }

  /**
   * Private method.
   */
  private function releaseLock() {
    variable_set("dkan_datastore_lock", 0);
  }

}
