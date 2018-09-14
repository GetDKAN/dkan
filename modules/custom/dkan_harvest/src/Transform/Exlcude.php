<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Transform;

class Exlcude extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Exclude', 'Running transform from Exclude');
    // TODO: Exclude.
    return $items;
  }
}
