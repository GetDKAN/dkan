<?php
/**
 * Created by PhpStorm.
 * User: fmizzell
 * Date: 10/14/18
 * Time: 7:50 PM
 */

namespace Dkan\Datastore\Manager;


class InfoProvider {
  private $info = [];

  public function addInfo(Info $info) {
    $this->info[] = $info;
  }

  public function getInfo() {
    return $this->info;
  }
}