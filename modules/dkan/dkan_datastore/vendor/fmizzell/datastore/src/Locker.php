<?php
/**
 * Created by PhpStorm.
 * User: fmizzell
 * Date: 10/14/18
 * Time: 8:06 PM
 */

namespace Dkan\Datastore;


class Locker
{
  private $lockName;

  public function __construct($name) {
    $this->lockName = $name;
  }

  /**
   * Private method.
   */
  public  function getLock() {
    $counter = 0;
    do {
      if ($counter >= 1) {
        sleep(1);
      }
      $success = @mkdir("/tmp/{$this->lockName}.lock", 0700);
      $counter++;
    } while (!$success);
  }

  /**
   * Private method.
   */
  public function releaseLock() {
    rmdir("/tmp/{$this->lockName}.lock");
  }

}