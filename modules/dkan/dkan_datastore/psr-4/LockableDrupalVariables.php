<?php

namespace Dkan\Datastore;

class LockableDrupalVariables {
  private $binName;

  public function __construct($bin_name) {
    $this->binName = $bin_name;
  }

  public function borrowBin() {
    $this->getLock();

    $bin = variable_get($this->binName, []);

    return $bin;
  }

  public function returnBin($bin) {
    variable_set($this->binName, $bin);

    $this->releaseLock();
  }

  public function set($id, $data) {
    $this->getLock();

    $bin = variable_get($this->binName, []);
    $bin[$id] = $data;
    variable_set($this->binName, $bin);

    $this->releaseLock();
  }

  public function get($id) {
    $this->getLock();

    $bin = variable_get($this->binName, []);

    $this->releaseLock();

    return isset($bin[$id]) ? $bin[$id] : NULL;
  }

  public function delete($id) {
    $this->getLock();

    $bin = variable_get($this->binName, []);
    unset($bin[$id]);
    variable_set($this->binName, $bin);

    $this->releaseLock();
  }

  private function getLock() {
    $counter = 0;
    do {
      if ($counter >= 1) {
        sleep(1);
      }
      $success = @mkdir('/tmp/dkan.lock', 0700);
      $counter++;
    }
    while (!$success) ;
  }

  private function releaseLock() {
    rmdir('/tmp/dkan.lock') ;
  }

}