<?php

namespace Drupal\dkan_harvest\Transform;

use Drupal\dkan_harvest\Transform;

class Def extends Transform {

  function run(&$items) {
    parent::run($items);
    $this->log->write('DEBUG', 'Def', 'Running transform from Def');
    // TODO: Add defaults.
    return $items;
  }
}
