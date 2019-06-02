<?php

namespace Dkan\Datastore;

use Dkan\Datastore\Storage\IKeyValue;

class LockableBinStorage {

  private $locker;
  private $keyValueStorer;

  private $binName;

  /**
   * LockableDrupalVariables constructor.
   */
  public function __construct($bin_name, Locker $locker, IKeyValue $key_value_store) {
    $this->locker = $locker;
    $this->keyValueStorer = $key_value_store;
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
    $this->locker->getLock();

    $bin = $this->keyValueStorer->get($this->binName, []);

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
    $this->keyValueStorer->set($this->binName, $bin);

    $this->locker->releaseLock();
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
    $this->locker->getLock();

    $bin = $this->keyValueStorer->get($this->binName, []);
    $bin[$id] = $data;
    $this->keyValueStorer->set($this->binName, $bin);

    $this->locker->releaseLock();
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
    $this->locker->getLock();

    $bin = $this->keyValueStorer->get($this->binName, []);

    $this->locker->releaseLock();

    return isset($bin[$id]) ? $bin[$id] : NULL;
  }

  /**
   * Delete the variable with the given id.
   *
   * @param string $id
   *   The id of the variable.
   */
  public function delete($id) {
    $this->locker->getLock();

    $bin = $this->keyValueStorer->get($this->binName, []);
    unset($bin[$id]);
    $this->keyValueStorer->set($this->binName, $bin);

    $this->locker->releaseLock();
  }

}
